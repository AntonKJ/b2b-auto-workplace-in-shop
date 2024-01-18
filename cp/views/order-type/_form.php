<?php

use common\models\Metro;
use common\models\OrderType;
use cp\models\OrderTypeForm;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\OrderType */
/* @var $formModel OrderTypeForm */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="order-type-group-form">

	<?php $form = ActiveForm::begin([
		'layout' => 'horizontal',
	]); ?>

	<?= $form->field($formModel, 'category')->dropDownList(OrderType::getCategoryOptions()) ?>

	<?php if (!$model->isNewRecord && $model->getIsCategoryCity()): ?>
		<?= $form->field($formModel, 'metro_id')->dropDownList(ArrayHelper::map(Metro::find()->defaultOrder()->all(), 'id', 'title'), [
			'prompt' => '~ Выберите метро',
		]) ?>
	<?php endif; ?>

	<div class="form-group">
		<hr>
		<?= Html::submitButton('Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>