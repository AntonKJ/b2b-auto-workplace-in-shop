<?php

use cp\models\DeliveryZone;

/* @var $this yii\web\View */
/* @var $model DeliveryZone */

$this->title = 'Редактирование зоны доставки: ' . $model->getTitle();

$this->params['breadcrumbs'][] = ['label' => 'Зоны доставки', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="order-type-group-update">

	<?= $this->render('_form', [
		'model' => $model,
	]) ?>

</div>
