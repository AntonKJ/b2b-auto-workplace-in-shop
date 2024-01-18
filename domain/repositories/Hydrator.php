<?php

namespace domain\repositories;

/**
 * Class Hydrator
 * @package core\repositories
 */
class Hydrator
{

	private $reflectionClassMap;

	/**
	 * @param $class
	 * @param array $data
	 * @return object
	 * @throws \InvalidArgumentException
	 * @throws \ReflectionException
	 */
	public function hydrate($class, array $data)
	{

		$reflection = $this->getReflectionClass($class);
		$target = $reflection->newInstanceWithoutConstructor();

		foreach ($data as $name => $value) {

			if (!$reflection->hasProperty($name))
				throw new \InvalidArgumentException("There's no {$name} property in {$class}.");

			$property = $reflection->getProperty($name);

			if ($property->isPrivate() || $property->isProtected())
				$property->setAccessible(true);

			$property->setValue($target, $value);
		}
		return $target;
	}

	/**
	 * @param $object
	 * @param array $data
	 * @return mixed
	 * @throws \InvalidArgumentException
	 * @throws \ReflectionException
	 */
	public function hydrateInto($object, array $data)
	{

		$className = get_class($object);
		$reflection = $this->getReflectionClass($className);

		foreach ($data as $name => $value) {

			if (!$reflection->hasProperty($name))
				throw new \InvalidArgumentException("There's no $name property in $className.");

			$property = $reflection->getProperty($name);

			if ($property->isPrivate() || $property->isProtected())
				$property->setAccessible(true);

			$property->setValue($object, $value);
		}
		return $object;
	}

	/**
	 * @param $object
	 * @param array $fields
	 * @return array
	 * @throws \ReflectionException
	 */
	public function extract($object, array $fields = [])
	{

		$result = [];

		$reflection = $this->getReflectionClass(\get_class($object));

		if ([] === $fields) {
			foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property)
				$fields[] = $property->getName();
		}

		foreach ($fields as $name) {

			$property = $reflection->getProperty($name);

			if ($property->isPrivate() || $property->isProtected())
				$property->setAccessible(true);

			$result[$property->getName()] = $property->getValue($object);
		}

		return $result;
	}

	/**
	 * @param $className
	 * @return \ReflectionClass
	 * @throws \ReflectionException
	 */
	private function getReflectionClass($className)
	{

		if (!isset($this->reflectionClassMap[$className]))
			$this->reflectionClassMap[$className] = new \ReflectionClass($className);

		return $this->reflectionClassMap[$className];
	}
}