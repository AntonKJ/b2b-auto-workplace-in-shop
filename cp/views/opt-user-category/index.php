<?php

use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Категории пользователей';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-user-category-index">

	<?= GridView::widget([
		'dataProvider' => $dataProvider,
		'columns' => [

			'id',
			'title',

			[
				'attribute' => 'order_type_group_id',
				'content' => function ($model) {

					if (null === $model->orderTypeGroup)
						return null;

					return $model->orderTypeGroup->title;
				},
			],

			[
				'class' => 'yii\grid\ActionColumn',
				'template' => '{update} {delete}',
			],
		],
	]); ?>
</div>
