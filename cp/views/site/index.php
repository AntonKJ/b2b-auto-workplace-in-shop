<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Админка';
$this->params['breadcrumbs'][] = $this->title;
?>

<ul class="list-unstyled">
	<li><?php echo \yii\helpers\Html::a('Управление зонами доставки', ['/delivery-zone/index']); ?></li>
	<li><?php echo \yii\helpers\Html::a('Управление регионами', ['/region/index']); ?></li>
	<li><?php echo \yii\helpers\Html::a('Управление типами заказов', ['/order-type/index']); ?></li>
	<li><?php echo \yii\helpers\Html::a('Управление группами заказов', ['/order-type-group/index']); ?></li>
	<li><?php echo \yii\helpers\Html::a('Управление городами доставки', ['/delivery-cities/index']); ?></li>
	<li><?php echo \yii\helpers\Html::a('Геометрия регионов', ['/region-geometry/index']); ?></li>
	<li><?php echo \yii\helpers\Html::a('Категории пользователей', ['/opt-user-category/index']); ?></li>
	<li><?php echo \yii\helpers\Html::a('Пользователи', ['/opt-user/index']); ?></li>
	<li><?php echo \yii\helpers\Html::a('Диски', ['/disk/index']); ?></li>
</ul>
