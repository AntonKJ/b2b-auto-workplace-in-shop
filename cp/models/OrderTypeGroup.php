<?php

namespace cp\models;

use common\models\OrderType;

class OrderTypeGroup extends \common\models\OrderTypeGroup
{

	public $orderTypesCount;
	protected $_orderTypeIds;

	/**
	 * @return mixed
	 */
	public function getOrderTypeIds($loadRelations = false)
	{

		if ($loadRelations)
			$this->_orderTypeIds = $this->getOrderTypes()
				->select('ot_id')
				->column();

		return $this->_orderTypeIds;
	}

	/**
	 * @param mixed $orderTypeIds
	 * @return OrderTypeGroup
	 */
	public function setOrderTypeIds($orderTypeIds)
	{
		$this->_orderTypeIds = $orderTypeIds;
		$this->saveOrderTypeRelations();

		return $this;
	}

	private function saveOrderTypeRelations()
	{

		$orderTypes = [];
		if (is_array($this->_orderTypeIds) && [] !== $this->_orderTypeIds)
			$orderTypes = OrderType::findAll($this->_orderTypeIds);

		$this->populateRelation('orderTypes', $orderTypes);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$rules = parent::rules();

		$rules[] = [['orderTypeIds'], 'required'];
		$rules[] = [['orderTypeIds'], 'exist', 'targetClass' => OrderType::class, 'targetAttribute' => 'ot_id', 'allowArray' => true];

		return $rules;
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		$labels = parent::attributeLabels();

		$labels['orderTypeIds'] = 'Типы заказов';
		$labels['orderTypesCount'] = 'Кол-во связаных типов заказов';

		return $labels;
	}

	public function afterSave($insert, $changedAttributes)
	{

		parent::afterSave($insert, $changedAttributes);

		$relatedRecords = $this->getRelatedRecords();

		if (!$insert)
			$this->unlinkAll('orderTypes', true);

		if (isset($relatedRecords['orderTypes'])) {

			foreach ($relatedRecords['orderTypes'] as $orderType)
				$this->link('orderTypes', $orderType);
		}

		return false;

	}

}
