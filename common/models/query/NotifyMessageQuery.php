<?php

namespace common\models\query;

use common\models\NotifyMessage;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[NotifyMessageQuery]].
 *
 * @see News
 */
class NotifyMessageQuery extends ActiveQuery
{

	/**
	 * @param int $status
	 * @return $this
	 */
	public function byStatus($status = NotifyMessage::STATUS_PUBLISHED): NotifyMessageQuery
	{
		$alias = $this->getAlias();
		return $this
			->andWhere([
				"{$alias}.[[status]]" => $status,
			]);
	}

	/**
	 * @return NotifyMessageQuery
	 */
	public function defaultOrder(): NotifyMessageQuery
	{
		$alias = $this->getAlias();
		return $this
			->addOrderBy([
				"{$alias}.[[is_pinned]]" => SORT_DESC,
				"{$alias}.[[id]]" => SORT_DESC,
			]);
	}

	/**
	 * @inheritdoc
	 * @return NotifyMessageQuery[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * @inheritdoc
	 * @return NotifyMessageQuery|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
