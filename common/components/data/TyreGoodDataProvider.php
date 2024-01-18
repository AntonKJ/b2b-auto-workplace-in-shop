<?php

namespace common\components\data;

use yii\data\ActiveDataProvider;

class TyreGoodDataProvider extends ActiveDataProvider
{

	/**
	 * @inheritdoc
	 */

	/*protected function prepareModels()
	{
		if (!$this->query instanceof QueryInterface) {
			throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
		}

		$query = clone $this->query;

		if (($sort = $this->getSort()) !== false) {
			$query->addOrderBy($sort->getOrders());
		}

		if (($pagination = $this->getPagination()) !== false) {

			$validatePage = $pagination->validatePage;
			$pagination->validatePage = false;

			$query->select($query->select, 'SQL_CALC_FOUND_ROWS');

			$query->limit($pagination->getLimit())->offset($pagination->getOffset());

			$result = $query->all($this->db);

			$count = \Yii::$app->db->createCommand('SELECT FOUND_ROWS()')->queryScalar();

			$pagination->totalCount = $count;

			$pagination->validatePage = $validatePage;

		} else
			$result = $query->all($this->db);

		return $result;
	}
	*/

}