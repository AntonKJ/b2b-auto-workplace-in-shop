<?php

namespace common\components;

use common\interfaces\OrderTypeGroupableInterface;
use common\interfaces\RegionEntityInterface;
use Yii;

class OrderTypeGroupGenerated implements OrderTypeGroupableInterface
{
	protected $_region;
	protected $_regionGroup;
	protected $_userGroup;

	public function __construct(RegionEntityInterface $region,
	                            OrderTypeGroupableInterface $regionOrderTypeGroup,
	                            OrderTypeGroupableInterface $userOrderTypeGroup)
	{
		$this->_region = $region;
		$this->_regionGroup = $regionOrderTypeGroup;
		$this->_userGroup = $userOrderTypeGroup;
	}

	/**
	 * @return int
	 */
	public function getOrderTypeGroupId()
	{

		$groupId = crc32(implode(',', [
			$this->_region->getId(),
			$this->_regionGroup->getOrderTypeGroupId(),
			$this->_userGroup->getOrderTypeGroupId(),
		]));

		Yii::info([
			'Вычисление виртуальной группы',
			'region_id' => $this->_region->getId(),
			'region_order_type_group_id' => $this->_regionGroup->getOrderTypeGroupId(),
			'user_order_type_group_id' => $this->_userGroup->getOrderTypeGroupId(),
			'generated_virtual_group_id' => $groupId,
		]);

		return $groupId;
	}

}
