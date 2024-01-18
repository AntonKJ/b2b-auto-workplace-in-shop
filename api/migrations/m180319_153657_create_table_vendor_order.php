<?php

namespace api\migrations;

use yii\db\Migration;

/**
 * Class m180319_153657_create_table_vendor_order
 */
class m180319_153657_create_table_vendor_order extends Migration
{
	public function safeUp()
	{

		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {

			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
		}

		$tableName = '{{%vendor_order}}';

		$this->createTable($tableName, [
			'id' => $this->primaryKey(),
			'vendor' => $this->char(16)->notNull(),
			'order_id' => $this->char(16)->notNull()->unique(),
			'status' => $this->string(32)->notNull(),
			'notified_status' => $this->string(32)->defaultValue(null),
			'updated_at' => $this->dateTime()->defaultValue(new \yii\db\Expression('current_timestamp')),
		], $tableOptions);

		$this->createIndex('vendor_order_unq_idx', $tableName, ['vendor', 'order_id']);
	}

	public function safeDown()
	{
		$tableName = '{{%vendor_order}}';
		$this->dropTable($tableName);
	}
}
