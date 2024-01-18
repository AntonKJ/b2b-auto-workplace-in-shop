<?php

use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Модели дисков';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="region-index">

	<?php echo GridView::widget([
		'dataProvider' => $dataProvider,
		'columns' => [

			'id',

			[
				'attribute' => 'title',
				'content' => function ($model) {
					return \yii\helpers\Html::a($model->title, ['view', 'id' => $model->id]);
				},
			],

		],
	]); ?>
</div>
