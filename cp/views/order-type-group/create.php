<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\OrderTypeGroup */

$this->title = 'Новая группа';
$this->params['breadcrumbs'][] = ['label' => 'Группы типов заказов', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-type-group-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
