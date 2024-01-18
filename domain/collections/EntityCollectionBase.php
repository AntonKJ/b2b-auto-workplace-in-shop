<?php

namespace domain\collections;

use domain\interfaces\EntityCollectionInterface;

abstract class EntityCollectionBase implements EntityCollectionInterface
{
	protected $data;

	final public function __construct(array $data = null)
	{

		$this->data = [];

		if (is_array($data))
			foreach ($data as $itm)
				$this->add($itm);
	}

	/**
	 * @return array
	 */
	final public function getAll()
	{
		return $this->data;
	}

	final protected function _add($data, $key = null)
	{
		if (null === $key)
			$this->data[] = $data;
		else
			$this->data[$key] = $data;
	}

	/**
	 * Returns an iterator for traversing the data.
	 * This method is required by the SPL interface [[\IteratorAggregate]].
	 * It will be implicitly called when you use `foreach` to traverse the collection.
	 * @return \ArrayIterator an iterator for traversing the cookies in the collection.
	 */
	final public function getIterator()
	{
		return new \ArrayIterator($this->data);
	}

	/**
	 * Returns the number of data items.
	 * This method is required by Countable interface.
	 * @return int number of data elements.
	 */
	final public function count()
	{
		return count($this->data);
	}

	/**
	 * This method is required by the interface [[\ArrayAccess]].
	 * @param mixed $offset the offset to check on
	 * @return bool
	 */
	final public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

	/**
	 * This method is required by the interface [[\ArrayAccess]].
	 * @param int $offset the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
	 */
	final public function offsetGet($offset)
	{
		return isset($this->data[$offset]) ? $this->data[$offset] : null;
	}

	/**
	 * This method is required by the interface [[\ArrayAccess]].
	 * @param int $offset the offset to set element
	 * @param mixed $item the element value
	 */
	final public function offsetSet($offset, $item)
	{
		$this->add($item, $offset);
	}

	/**
	 * This method is required by the interface [[\ArrayAccess]].
	 * @param mixed $offset the offset to unset element
	 */
	final public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}
}