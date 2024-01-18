<?php

namespace domain\repositories\ar;

use domain\entities\order\OrderType;
use domain\entities\order\OrderTypeCollection;
use domain\entities\order\OrderTypeEntityCollectionInterface;
use domain\interfaces\OrderTypeRepositoryInterface;
use domain\repositories\Hydrator;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;

/**
 * Class OrderTypeRepository
 * @package domain\repositories\ar
 * @deprecated
 */
class OrderTypeRepository extends RepositoryBase implements OrderTypeRepositoryInterface
{

	private $hydrator;

	protected $orderTypeModelClass;

	function __construct(ActiveRecordInterface $orderTypeModelClass, Hydrator $hydrator)
	{

		$this->orderTypeModelClass = $orderTypeModelClass;
		$this->hydrator = $hydrator;

	}

	protected function _populateEntity(array $data): OrderType
	{
		return $this->hydrator->hydrate(OrderType::class, [
			'id' => (int)$data['ot_id'],
			'title' => $data['name'],
			'from_shop_id' => (int)$data['from_shop_id'],
			'days' => (int)$data['days'],
			'sortorder' => (int)$data['ord_num'],
		]);
	}

	public function findAll(): OrderTypeEntityCollectionInterface
	{
		/* @var $class ActiveRecord */
		$class = $this->orderTypeModelClass;

		$reader = $class::find()
			->orderBy(['ord_num' => SORT_ASC])
			->asArray();

		$data = new OrderTypeCollection();

		foreach ($reader->each() as $row)
			$data[] = $this->_populateEntity($row);

		return $data;
	}

}