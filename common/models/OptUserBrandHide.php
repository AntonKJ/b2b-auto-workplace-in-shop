<?php

namespace common\models;

/**
 * This is the model class for table "{{%opt_users_brands_hide}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $entity_type
 * @property string $entity_code
 * @property string $created_at
 *
 * @deprecated use OptUserProducerRestrict instead
 */
class OptUserBrandHide extends \yii\db\ActiveRecord
{

	const ENTITY_TYPE_TYRE = 'tyre';
	const ENTITY_TYPE_DISK = 'disk';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%opt_users_brands_hide}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'entity_type', 'entity_code'], 'required'],
            [['user_id'], 'integer'],
            [['created_at'], 'safe'],
            [['entity_type'], 'string', 'max' => 6],
            [['entity_code'], 'string', 'max' => 32],
            [['user_id', 'entity_type', 'entity_code'], 'unique', 'targetAttribute' => ['user_id', 'entity_type', 'entity_code'], 'message' => 'The combination of User ID, Entity Type and Entity Code has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'entity_type' => 'Entity Type',
            'entity_code' => 'Entity Code',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\OptUsersBrandsHideQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\OptUsersBrandsHideQuery(get_called_class());
    }

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUser()
	{
		return $this->hasOne(OptUser::class, ['id' => 'user_id']);
	}
}
