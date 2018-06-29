<?php
/**
 * Star Ratings plugin for Craft CMS
 *
 * An easy to use and highly flexible ratings system.
 *
 * @author    Double Secret Agency
 * @link      https://www.doublesecretagency.com/
 * @copyright Copyright (c) 2015 Double Secret Agency
 */

namespace doublesecretagency\starratings\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\MigrationHelper;

/**
 * Migration: Add key column
 * @since 2.0.0
 */
class m151113_000000_starRatings_addKeyColumn extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->_addStarKeyColumn('{{%starratings_elementratings}}', 'id');
        $this->_addStarKeyColumn('{{%starratings_ratinglog}}', 'elementId');
        $this->_addElementIdColumn();
        $this->_copyForeignKeyData();
        $this->_removeZeros();
        $this->_cleanupColumns();
        $this->_renumberPrimaryKey();
    }

    private function _addStarKeyColumn($table, $after)
    {
        if (!$this->db->columnExists($table, 'starKey')) {
            $this->addColumn($table, 'starKey', $this->string()->after($after));
        }
    }

    private function _addElementIdColumn()
    {
        if (!$this->db->columnExists('{{%starratings_elementratings}}', 'elementId')) {
            $this->addColumn('{{%starratings_elementratings}}', 'elementId', $this->integer()->after('id'));
        }
        $this->addForeignKey(null, '{{%starratings_elementratings}}', ['elementId'], '{{%elements}}', ['id'], 'CASCADE');
    }

    private function _copyForeignKeyData()
    {
        // Get data
        $oldData = (new Query())
            ->select(['id'])
            ->from(['{{%starratings_elementratings}}'])
            ->orderBy('id')
            ->all($this->db);
        // Copy data
        foreach ($oldData as $row) {
            $newData = ['elementId' => $row['id']];
            $this->update('{{%starratings_elementratings}}', $newData, ['id' => $row['id']]);
        }
        // After values have been transferred, disallow null elementId values
        $this->alterColumn('{{%starratings_elementratings}}', 'elementId', $this->integer()->notNull());
    }

    private function _removeZeros()
    {
        $this->delete('{{%starratings_elementratings}}', 'totalVotes=0');
    }

    private function _cleanupColumns()
    {
        MigrationHelper::dropForeignKeyIfExists('{{%starratings_elementratings}}', ['id'], $this);
        $this->alterColumn('{{%starratings_elementratings}}', 'id', $this->integer().' NOT NULL AUTO_INCREMENT');
    }

    private function _renumberPrimaryKey()
    {
        // Get data
        $oldData = (new Query())
            ->select(['elementId'])
            ->from(['{{%starratings_elementratings}}'])
            ->orderBy('id')
            ->all($this->db);
        // Renumber rows
        $i = 1;
        foreach ($oldData as $row) {
            $newData = ['id' => $i++];
            $this->update('{{%starratings_elementratings}}', $newData, ['elementId' => $row['elementId']]);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m151113_000000_starRatings_addKeyColumn cannot be reverted.\n";

        return false;
    }

}