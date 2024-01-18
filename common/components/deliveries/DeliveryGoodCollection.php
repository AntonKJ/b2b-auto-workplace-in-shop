<?php

namespace common\components\deliveries;

use ArrayAccess;
use Countable;
use Iterator;
use yii\base\InvalidCallException;

class DeliveryGoodCollection implements ArrayAccess, Iterator, Countable
{

	private $_data;
	private $_iteratorData;

	public function __construct(array $data = [])
	{
		$this->_data = [];
		if ([] !== $data) {
			foreach ($data as $itm) {
				if (!isset($itm['id'], $itm['quantity'])) {
					throw new InvalidCallException('Collection item is invalid.');
				}
				$this->addGood((string)$itm['id'], (int)$itm['quantity']);
			}
		}
	}

	public function getData()
	{
		return $this->_data;
	}

	/**
	 * @param string $goodId
	 * @param int $quantity
	 * @return $this
	 */
	public function addGood(string $goodId, int $quantity): self
	{
		$this->_data[] = [
			'id' => $goodId,
			'quantity' => $quantity,
		];
		return $this;
	}

	public function offsetExists($offset)
	{
		return isset($this->getData()[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->getData()[$offset];
	}

	public function offsetSet($offset, $value)
	{
		throw new InvalidCallException('Collection is readonly.');
	}

	public function offsetUnset($offset)
	{
		throw new InvalidCallException('Collection is readonly.');
	}

	public function current()
	{
		if ($this->_iteratorData === null) {
			$this->_iteratorData = $this->getData();
		}
		return current($this->_iteratorData);
	}

	public function next()
	{
		if ($this->_iteratorData === null) {
			$this->_iteratorData = $this->getData();
		}
		next($this->_iteratorData);
	}

	public function key()
	{
		if ($this->_iteratorData === null) {
			$this->_iteratorData = $this->getData();
		}
		return key($this->_iteratorData);
	}

	public function valid()
	{

		if ($this->_iteratorData === null) {
			$this->_iteratorData = $this->getData();
		}
		return current($this->_iteratorData) !== false;
	}

	public function rewind()
	{
		$this->_iteratorData = $this->getData();
		reset($this->_iteratorData);
	}

	public function count()
	{
		return \count($this->getData());
	}
}
