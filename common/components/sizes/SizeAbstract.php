<?php

namespace common\components\sizes;

use ReflectionClass;
use yii\base\Component;

/**
 * Class SizeAbstract
 * @package common\components\sizes
 * @deprecated
 */
abstract class SizeAbstract extends Component
{

	protected $_parts;

	/**
	 * @return mixed
	 */
	public function getParts()
	{
		return $this->_parts;
	}

	/**
	 * @param mixed $parts
	 */
	public function setParts($parts)
	{
		$this->_parts = $parts;
	}

	public static function parseFromString($str)
	{
		new \Exception('Implement parseFromString method in ' . __CLASS__);
	}

	/**
	 * @param $str
	 * @return array[parts => [], sizes => []]
	 * @throws \Exception
	 */
	public static function createFromString($str)
	{
		new \Exception('Implement createFromString method in ' . __CLASS__);
	}

	abstract public function format();

	/**
	 * Returns the list of attribute names.
	 * By default, this method returns all public non-static properties of the class.
	 * You may override this method to change the default behavior.
	 * @return array list of attribute names.
	 */
	public function attributes()
	{

		$class = new ReflectionClass($this);

		$names = [];

		foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {

			if (!$property->isStatic()) {

				$names[] = $property->getName();
			}
		}

		return $names;
	}

	/**
	 * Returns attribute values.
	 * @param array $names list of attributes whose value needs to be returned.
	 * Defaults to null, meaning all attributes listed in [[attributes()]] will be returned.
	 * If it is an array, only the attributes in the array will be returned.
	 * @param array $except list of attributes whose value should NOT be returned.
	 * @return array attribute values (name => value).
	 */
	public function getAttributes($names = null, $except = [])
	{

		$values = [];

		if ($names === null) {

			$names = $this->attributes();
		}

		foreach ($names as $name) {

			$values[$name] = $this->$name;
		}

		foreach ($except as $name) {

			unset($values[$name]);
		}

		return $values;
	}

	/**
	 * @return array
	 */
	public function getFilledSizeParts()
	{

		return array_filter($this->getAttributes(), function ($v) {
			return null != $v;
		});
	}

}