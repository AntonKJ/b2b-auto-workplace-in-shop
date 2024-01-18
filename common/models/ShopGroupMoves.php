<?php

namespace common\models;

use common\components\ecommerce\models\ShopGroupMove as ShopGroupMoveEntity;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%shop_group_moves}}".
 *
 * @property integer $move_id
 * @property integer $shop_group_from
 * @property integer $shop_group_to
 * @property integer $move_days
 * @property integer $move_mins
 */
class ShopGroupMoves extends ActiveRecord
{
	/**
	 * @var ShopGroupMoveEntity
	 */
	private $_ecommerceEntity;

	public function getEcommerceEntity(): ShopGroupMoveEntity
	{
		if ($this->_ecommerceEntity === null) {
			$this->_ecommerceEntity = new ShopGroupMoveEntity(
				(int)$this->move_id,
				(int)$this->shop_group_from,
				(int)$this->shop_group_to,
				(int)$this->move_days,
				(int)$this->move_mins
			);
		}
		return $this->_ecommerceEntity;
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%shop_group_moves}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['shop_group_from', 'shop_group_to', 'move_days'], 'integer'],
		];
	}

}
