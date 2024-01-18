<?php

use cp\models\OrderTypeGroup;
use cp\models\RegionSearch;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel RegionSearch */

$this->title = 'Регионы';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="region-index">

	<?= GridView::widget([
		'dataProvider' => $dataProvider,
		'filterModel' => $searchModel,
		'columns' => [

			[
				'attribute' => 'region_id',
			],

			'name',
			'url_frag',
			'region_group_id',
			'zone_id',
			'alt_zone_id',

			[
				'attribute' => 'order_type_group_id',
				'filter' => ArrayHelper::map(OrderTypeGroup::find()->orderBy(['title' => SORT_ASC])->all(), 'id', 'title'),
				'value' => static function ($model) {
					if ($model->orderTypeGroup === null) {
						return null;
					}
					return $model->orderTypeGroup->title;
				},
			],
			[
				'class' => ActionColumn::class,
				'template' => '{update}',
			],
		],
	]); ?>
</div>
