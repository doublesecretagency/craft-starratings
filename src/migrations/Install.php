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

/**
 * Installation Migration
 * @since 2.0.0
 */
class Install extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp(): void
    {
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): void
    {
        $this->dropTableIfExists('{{%starratings_elementratings}}');
        $this->dropTableIfExists('{{%starratings_ratinglog}}');
        $this->dropTableIfExists('{{%starratings_userhistories}}');
    }

    /**
     * Creates the tables.
     */
    protected function createTables(): void
    {
        $this->createTable('{{%starratings_elementratings}}', [
            'id'          => $this->primaryKey(),
            'elementId'   => $this->integer()->notNull(),
            'starKey'     => $this->string(),
            'avgRating'   => $this->decimal(5, 3),
            'totalVotes'  => $this->integer(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid'         => $this->uid(),
        ]);
        $this->createTable('{{%starratings_ratinglog}}', [
            'id'            => $this->primaryKey(),
            'elementId'     => $this->integer()->notNull(),
            'starKey'       => $this->string(),
            'userId'        => $this->integer(),
            'ipAddress'     => $this->string(),
            'ratingValue'   => $this->tinyInteger(2),
            'ratingChanged' => $this->boolean()->defaultValue(false),
            'dateCreated'   => $this->dateTime()->notNull(),
            'dateUpdated'   => $this->dateTime()->notNull(),
            'uid'           => $this->uid(),
        ]);
        $this->createTable('{{%starratings_userhistories}}', [
            'id'          => $this->integer()->notNull(),
            'history'     => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid'         => $this->uid(),
            'PRIMARY KEY([[id]])',
        ]);
    }

    /**
     * Creates the indexes.
     */
    protected function createIndexes(): void
    {
        $this->createIndex(null, '{{%starratings_elementratings}}', ['elementId']);
        $this->createIndex(null, '{{%starratings_ratinglog}}',      ['elementId']);
    }

    /**
     * Adds the foreign keys.
     */
    protected function addForeignKeys(): void
    {
        $this->addForeignKey(null, '{{%starratings_elementratings}}', ['elementId'], '{{%elements}}', ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%starratings_ratinglog}}',      ['elementId'], '{{%elements}}', ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%starratings_userhistories}}',  ['id'],        '{{%users}}',    ['id'], 'CASCADE');
    }

}
