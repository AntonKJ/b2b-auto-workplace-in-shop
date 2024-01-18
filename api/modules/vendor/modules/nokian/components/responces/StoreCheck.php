<?php

namespace api\modules\vendor\modules\nokian\components\responces;

use api\modules\vendor\modules\nokian\models\forms\OrderAvailableForm;
use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;

class StoreCheck extends Component implements Arrayable
{

	use ArrayableTrait;

	/**
	 * @var OrderAvailableForm
	 */
	protected $orderAvailable;

	public function __construct(OrderAvailableForm $orderAvailable, array $config = [])
	{
		parent::__construct($config);
		$this->orderAvailable = $orderAvailable;
	}

	/**
	 * @return string
	 * @throws InvalidConfigException
	 * @throws NotInstantiableException
	 */
	public function toXmlString(): string
	{
		$out = [];
		foreach ($this->orderAvailable->getAvailability() as $good) {
			$quantityItems = [];
			foreach ($good['quantity'] as $key => $value) {
				$quantityItems[] = sprintf('<quantity>%d</quantity>', $value);
			}
			$out[] = sprintf('<product><code>%s</code>%s</product>', $good['code'], implode('', $quantityItems));
		}
		return '<?xml version="1.0" encoding="UTF-8"?><response>' . implode('', $out) . '</response>';
	}

	/**
	 * @return string
	 * @throws InvalidConfigException
	 * @throws NotInstantiableException
	 */
	public function __toString()
	{
		return $this->toXmlString();
	}

	public function fields()
	{
		return [

		];
	}

}
