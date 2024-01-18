<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\OrderTypeGroup */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="order-type-group-form">

	<?php $form = ActiveForm::begin([
		'layout' => 'horizontal',
	]); ?>

	<?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

	<?php
	echo $form->field($model, 'orderTypeIds')
		->checkboxList(ArrayHelper::map(\common\models\OrderType::find()->defaultOrder()->all(), 'id', function ($model) {
			return "[{$model->id}] <span class=\"label label-info\">{$model->category}</span> {$model->name}";
		}), [
			'encode' => false,
		]); ?>

	<div class="form-group">
		<hr>
		<?= Html::submitButton('Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>
