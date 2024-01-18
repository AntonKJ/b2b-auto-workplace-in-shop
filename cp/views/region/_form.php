<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Region */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="order-type-group-form">

	<?php $form = ActiveForm::begin([
		'layout' => 'horizontal',
	]); ?>

	<?= $form->field($model, 'order_type_group_id')->dropDownList(ArrayHelper::map(\common\models\OrderTypeGroup::find()->all(), 'id', 'title'), [
		'prompt' => '~ Укажите группу',
	]) ?>

	<?php echo $form->field($model, 'shops_from_region_id'); ?>

	<div class="form-group">
		<hr>
		<?= Html::submitButton('Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>
