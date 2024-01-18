<?php

use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Бренды дисков';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="region-index">

	<?php echo GridView::widget([
		'dataProvider' => $dataProvider,
		'columns' => [

			'id',

			[
				'attribute' => 'name',
				'content' => function ($model) {
					return \yii\helpers\Html::a($model->name, ['view', 'id' => $model->id]);
				},
			],

		],
	]); ?>
</div>
