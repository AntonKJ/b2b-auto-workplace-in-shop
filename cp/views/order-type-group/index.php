<?php

use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Группы типов заказов';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-type-group-index">

	<p>
		<?= Html::a('Новая группа', ['create'], ['class' => 'btn btn-success']) ?>
	</p>
	<?= GridView::widget([
		'dataProvider' => $dataProvider,
		'columns' => [
			['class' => 'yii\grid\SerialColumn'],

			'id',
			'title',
			'orderTypesCount',

			[
				'class' => 'yii\grid\ActionColumn',
				'template' => '{update} {delete}',
			],
		],
	]); ?>
</div>
