<?php

/* @var $this yii\web\View */
/* @var $model common\models\OrderTypeGroup */

$this->title = 'Категория пользователей: ' . $model->title;

$this->params['breadcrumbs'][] = ['label' => 'Категория пользователей: ' . $model->title, 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="order-user-category-update">

	<?= $this->render('_form', [
		'model' => $model,
	]) ?>

</div>
