<?php

/* @var $this yii\web\View */
/* @var $model common\models\OrderTypeGroup */
/* @var $formModel \cp\models\OrderTypeForm */

$this->title = 'Редактирование типа заказов: ' . $model->name;

$this->params['breadcrumbs'][] = ['label' => 'Типы заказов', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="order-type-group-update">

	<?= $this->render('_form', [
		'model' => $model,
		'formModel' => $formModel,
	]) ?>

</div>
