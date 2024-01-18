<?php

use common\models\OptUserCategory;
use common\models\Region;
use cp\models\OptUser;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel OptUser */
/** @var yii\web\View $this */

$this->title = 'Категории пользователей';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-user-category-index">

	<?= GridView::widget([
		'dataProvider' => $dataProvider,
		'filterModel' => $searchModel,
		'columns' => [

			'id',
			'fullname',
			[
				'attribute' => 'email',
			],
			'code_1c',

			[
				'attribute' => 'ou_category_id',
				'filter' => ArrayHelper::map(OptUserCategory::find()->orderBy(['ou_category_id' => SORT_ASC])->all(), 'id', 'title'),
				'value' => static function (OptUser $model) {
					return $model->category->title;
				},
			],

			[
				'attribute' => 'region_id',
				'filter' => ArrayHelper::map(Region::find()->orderBy(['name' => SORT_ASC])->all(), 'id', static function ($model) {
					return sprintf('%s [%d]', $model->title, $model->id);
				}),
				'value' => static function (OptUser $model) {
					return sprintf('%s [%d]', $model->region->title, $model->region->id);
				},
			],

			[
				'attribute' => 'tyre_brand_restrict',
				'filter' => ArrayHelper::map(\common\models\TyreBrand::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'title'),
				'value' => static function (OptUser $model) {
					return implode(', ', ArrayHelper::getColumn($model->tyreBrandRestrict, 'title'));
				},
			],

			[
				'class' => ActionColumn::class,
				'template' => '{view}',
			],
		],
	]); ?>
</div>
