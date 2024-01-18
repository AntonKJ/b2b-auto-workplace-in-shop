<?php

namespace domain\traits;

use domain\helpers\ArrayHelper;
use function in_array;
use function is_callable;
use function is_int;

trait ArrayableTrait
{

	public function fields()
	{
		$fields = array_keys(get_object_vars($this));
		return array_combine($fields, $fields);
	}

	public function extraFields()
	{
		return [];
	}

	public function toArray(array $fields = [], array $expand = [], $recursive = true)
	{
		$data = [];
		foreach ($this->resolveFields($fields, $expand) as $field => $definition) {
			$data[$field] = is_callable($definition) ? $definition($this, $field) : $definition;
		}
		return $recursive ? ArrayHelper::toArray($data) : $data;
	}

	protected function resolveFields(array $fields, array $expand)
	{
		$result = [];

		foreach ($this->fields() as $field => $definition) {
			if (is_int($field)) {
				$field = $definition;
			}
			if (empty($fields) || in_array($field, $fields, true)) {
				$result[$field] = $definition;
			}
		}

		if (empty($expand)) {
			return $result;
		}

		foreach ($this->extraFields() as $field => $definition) {
			if (is_int($field)) {
				$field = $definition;
			}
			if (in_array($field, $expand, true)) {
				$result[$field] = $definition;
			}
		}

		return $result;
	}
}
