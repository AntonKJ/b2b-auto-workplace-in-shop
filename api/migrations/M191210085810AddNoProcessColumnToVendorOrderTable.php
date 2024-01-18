<?php

namespace api\migrations;

use yii\db\Migration;

/**
 * Class M191210085810AddNoProcessColumnToVendorOrderTable
 */
class M191210085810AddNoProcessColumnToVendorOrderTable extends Migration
{

	public const TABLE_NAME = '{{%vendor_order}}';

	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->addColumn(static::TABLE_NAME, 'attempts', $this->smallInteger()->notNull()->defaultValue(0));
		$this->createIndex('vendor_order_attempts', static::TABLE_NAME, ['attempts']);
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn(static::TABLE_NAME, 'attempts');
	}

}
