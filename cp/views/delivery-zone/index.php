<?php

use cp\models\DeliveryZone;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Зоны доставки';
$this->params['breadcrumbs'][] = $this->title;
?>
<div>

	<?= GridView::widget([
		'dataProvider' => $dataProvider,
		'columns' => [
			'id',
			'title',
			'email',
			'color',
			'is_published',
			[
				'attribute' => 'order_type_id',
				'content' => static function (DeliveryZone $model) {
					if ($model->orderType === null) {
						return '&mdash;';
					}
					return "[{$model->order_type_id}] [{$model->orderType->category}] {$model->orderType->title}";
				},
			],
			[
				'header' => 'Есть область',
				'content' => static function (DeliveryZone $model) {
					return !empty($model->delivery_area) ? 'Да' : '&mdash;';
				},
			],
			[
				'header' => 'Города',
				'content' => static function (DeliveryZone $model) {
					return $model->citiesCount > 0 ? $model->citiesCount : '&mdash;';
				},
			],
			[
				'class' => ActionColumn::class,
				'template' => '{update}',
			],
		],
	]); ?>
</div>
