<?php

use cp\models\DeliveryCitySearch;
use cp\models\DeliveryZone;
use cp\models\OrderTypeGroup;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel DeliveryCitySearch */

$this->title = 'Города доставки';
$this->params['breadcrumbs'][] = $this->title;
?>
<div>

	<?= GridView::widget([
		'dataProvider' => $dataProvider,
		'filterModel' => $searchModel,
		'columns' => [
			'id',
			'City',
			'lat',
			'lng',
			'delivery_days',
			'delivery_area_radius',
			[
				'header' => 'Зона привязки города',
				'attribute' => 'zone_id',
				'filter' => ArrayHelper::map(DeliveryZone::find()->orderBy(['Name' => SORT_ASC])->all(), 'id', static function($zoneModel){
					return sprintf('%s [%d]', $zoneModel->getTitle(), $zoneModel->id);
				}),
				'value' => static function (DeliveryCitySearch $model) {
					return Inflector::sentence(ArrayHelper::getColumn($model->zones, static function ($zoneModel) {
						return sprintf('%s [%d]', $zoneModel->getTitle(), $zoneModel->id);
					}));
				},
			],
			[
				'class' => ActionColumn::class,
				'template' => '{update}',
			],
		],
	]); ?>
</div>
