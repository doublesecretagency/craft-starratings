<?php
namespace Craft;

class m151113_000000_starRatings_addKeyColumn extends BaseMigration
{

	public function safeUp()
	{
		$this->_addKeyColumns();
		$table = 'starratings_elementratings';
		$this->_addElementIdColumn($table);
		$this->_copyForeignKeyData($table);
		$this->_cleanupColumns($table);
		$this->_renumberPrimaryKey($table);
		return true;
	}

	private function _addKeyColumns()
	{
		$this->addColumnAfter('starratings_elementratings', 'starKey', ColumnType::Varchar, 'id');
		$this->addColumnAfter('starratings_ratinglog',      'starKey', ColumnType::Varchar, 'elementId');
	}

	private function _addElementIdColumn($table)
	{
		$this->addColumnAfter($table, 'elementId', ColumnType::Int, 'id');
		$this->addForeignKey($table, 'elementId', 'elements', 'id', 'CASCADE', 'CASCADE');
	}

	private function _copyForeignKeyData($table)
	{
		// Get data
		$query = craft()->db->createCommand()
			->select()
			->from($table)
			->order('id')
		;
		$oldData = $query->queryAll();
		// Copy data
		foreach ($oldData as $row) {
			$newData = array(
				'elementId' => $row['id'],
			);
			$this->update($table, $newData, 'id=:id', array(':id'=>$row['id']));
		}
	}

	private function _cleanupColumns($table)
	{
		$this->dropForeignKey($table, 'id');
		$this->alterColumn($table, 'elementId', array('column' => ColumnType::Int, 'required' => true));
		$this->alterColumn($table, 'id', array('column' => ColumnType::Int.' AUTO_INCREMENT', 'required' => true));
	}

	private function _renumberPrimaryKey($table)
	{
		// Get data
		$query = craft()->db->createCommand()
			->select()
			->from($table)
			->order('id')
		;
		$oldData = $query->queryAll();
		// Renumber rows
		$i = 1;
		foreach ($oldData as $row) {
			$newData = array(
				'id' => $i++,
			);
			$this->update($table, $newData, 'elementId=:elementId', array(':elementId'=>$row['elementId']));
		}
	}

}
