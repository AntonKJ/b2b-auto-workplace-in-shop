<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\OrderTypeGroup */

$this->title = 'Редактирование группы: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Группы типов заказов', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="order-type-group-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
