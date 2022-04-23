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
use craft\helpers\Db;
use yii\base\NotSupportedException;

/**
 * Migration: Add key column
 * @since 2.0.0
 */
class m151113_000000_starRatings_addKeyColumn extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp(): void
    {
        $this->_addStarKeyColumn('{{%starratings_elementratings}}', 'id');
        $this->_addStarKeyColumn('{{%starratings_ratinglog}}', 'elementId');
        $this->_addElementIdColumn();
        $this->_copyForeignKeyData();
        $this->_removeZeros();
        $this->_cleanupColumns();
        $this->_renumberPrimaryKey();
    }

    /**
     * Adds key column to specified table.
     *
     * @param string $table
     * @param string $after
     * @throws NotSupportedException
     */
    private function _addStarKeyColumn(string $table, string $after): void
    {
        if (!$this->db->columnExists($table, 'starKey')) {
            $this->addColumn($table, 'starKey', $this->string()->after($after));
        }
    }

    /**
     * Adds an element ID column.
     *
     * @throws NotSupportedException
     */
    private function _addElementIdColumn(): void
    {
        if (!$this->db->columnExists('{{%starratings_elementratings}}', 'elementId')) {
            $this->addColumn('{{%starratings_elementratings}}', 'elementId', $this->integer()->after('id'));
        }
        $this->addForeignKey(null, '{{%starratings_elementratings}}', ['elementId'], '{{%elements}}', ['id'], 'CASCADE');
    }

    /**
     * Copies the foreign key data.
     */
    private function _copyForeignKeyData(): void
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

    /**
     * Remove records with zero total votes.
     */
    private function _removeZeros(): void
    {
        $this->delete('{{%starratings_elementratings}}', 'totalVotes=0');
    }

    /**
     * Clean up columns.
     */
    private function _cleanupColumns(): void
    {
        Db::dropForeignKeyIfExists('{{%starratings_elementratings}}', ['id'], $this);
        $this->alterColumn('{{%starratings_elementratings}}', 'id', $this->integer().' NOT NULL AUTO_INCREMENT');
    }

    /**
     * Renumber the primary key.
     */
    private function _renumberPrimaryKey(): void
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
    public function safeDown(): bool
    {
        echo "m151113_000000_starRatings_addKeyColumn cannot be reverted.\n";

        return false;
    }

}
