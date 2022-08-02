<?php

use yii\db\Migration;
use yii\db\mssql\Schema;

/**
 * Class m220801_161405_tenders
 */
class m220801_161405_tenders extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$tableOptions = '';
		if ($this->db->driverName === 'mysql') {
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%tenders}}', [
			'id' => Schema::TYPE_PK. ' AUTO_INCREMENT',
			'tender_id' => Schema::TYPE_STRING.' NOT NULL',
			'date_modified' => Schema::TYPE_DATETIME.' NULL',
			'created_at' => Schema::TYPE_DATETIME.' NULL DEFAULT CURRENT_TIMESTAMP',
			'updated_at' => Schema::TYPE_DATETIME.' NULL DEFAULT CURRENT_TIMESTAMP',
		], $tableOptions);
	}

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropTable('{{%tenders}}');
    }
}
