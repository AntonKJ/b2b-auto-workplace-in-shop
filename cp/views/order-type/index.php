<?php

use common\models\DeliveryZone;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Типы заказов';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="region-index">

	<?= GridView::widget([
		'dataProvider' => $dataProvider,
		'columns' => [
			//['class' => 'yii\grid\SerialColumn'],

			'id',
			'name',
			'from_shop_id',
			'days',
			'ord_num',
			[
				'attribute' => 'category',
				'content' => function (\cp\models\OrderType $model) {

					if (empty($model->category))
						return '&mdash;';

					$out = ['<div>' . $model->getCategoryText() . '</div>'];
					$out[] = '<small class="text-muted">' . $model->category . '</small>';

					return implode("\n", $out);
				},
			],
			[
				'header' => 'Зоны',
				'content' => function (\cp\models\OrderType $model) {
					return $model->zonesCount > 0 ? $model->zonesCount : '&mdash;';
				},
			],
			[
				'attribute' => 'deliveryZones',
				'content' => function ($model) {

					if ($model->deliveryZones === []) {
						return '&mdash;';
					}

					return "<ol>\n<li>" . implode("</li>\n<li>", \yii\helpers\ArrayHelper::getColumn($model->deliveryZones, function (DeliveryZone $model) {
							return "[{$model->id}] {$model->title}";
						}, false)) . "</li>\n</ol>";
				},
			],
			[
				'header' => 'Есть метро',
				'content' => function (\cp\models\OrderType $model) {
					return $model->metro !== null ? 'Да' : '&mdash;';
				},
			],
			[
				'header' => 'Города',
				'content' => function (\cp\models\OrderType $model) {
					return $model->citiesCount > 0 ? $model->citiesCount : '&mdash;';
				},
			],
			[
				'attribute' => 'group',
				'content' => function ($model) {

					if ($model->groups === []) {
						return '&mdash;';
					}

					return "<ol>\n<li>" . implode("</li>\n<li>", \yii\helpers\ArrayHelper::map($model->groups, 'id', 'title')) . "</li>\n</ol>";
				},
			],

			[
				'class' => 'yii\grid\ActionColumn',
				'template' => '{update}',
			],
		],
	]); ?>
</div>
