<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%notify_message_region}}".
 *
 * @property int|null $id
 * @property string $title
 */
class NotifyMessageRegion extends ActiveRecord
{

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%notify_message_region}}';
	}

}
