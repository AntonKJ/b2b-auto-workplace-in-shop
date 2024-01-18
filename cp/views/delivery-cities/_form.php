<?php

use yii\bootstrap\ActiveForm;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\OrderType */
/* @var $formModel \cp\models\OrderTypeForm */
/* @var $form yii\widgets\ActiveForm */
?>

	<div class="order-type-group-form">

		<?php $form = ActiveForm::begin([
			'layout' => 'horizontal',
		]); ?>

		<?= $form->field($formModel, 'category')->dropDownList(\common\models\OrderType::getCategoryOptions()) ?>

		<?php if (!$model->isNewRecord && $model->getIsCategoryCity()): ?>
			<?= $form->field($formModel, 'metro_id')->dropDownList(\yii\helpers\ArrayHelper::map(\common\models\Metro::find()->defaultOrder()->all(), 'id', 'title'), [
				'prompt' => '~ Выберите метро'
			]) ?>
		<?php endif; ?>

		<div class="form-group">
			<hr>
			<?= Html::submitButton('Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
		</div>

		<?php ActiveForm::end(); ?>

	</div>

<?php if (!$model->isNewRecord && $model->getIsCategoryRegion()): ?>
	<hr>
	<h3>Города области доставки</h3>

	<?php
	$cityModel = new \cp\models\OrderTypeDeliveryCityForm();
	$form = ActiveForm::begin([
		'layout' => 'inline',
		'options' => [
			'style' => 'margin-bottom: 1em;',
		],
		'action' => ['city-add', 'id' => $model->id],
	]);
	?>

	<fieldset>

		<legend>Добавить город</legend>

		<?php echo $form->errorSummary($cityModel); ?>

		<?= $form->field($cityModel, 'city_id')
			->dropDownList(ArrayHelper::map(\common\models\DeliveryCity::find()
				->andWhere(['not in', 'id', $model->getCities()->select('id')->column()])
				->defaultOrder()
				->all(), 'id', 'title'), [
						'prompt' => '~ Выберите город'
			]) ?>

		<div class="form-group">
			<?= Html::submitButton('Добавить город', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
		</div>

	</fieldset>

	<?php ActiveForm::end(); ?>

	<?= GridView::widget([
		'dataProvider' => new \yii\data\ActiveDataProvider([
			'query' => $model->getCities()->defaultOrder(),
			'sort' => false,
			'pagination' => false,
		]),
		'columns' => [

			'id',
			'title',

			[
				'class' => 'yii\grid\ActionColumn',
				'template' => '{delete}',
				'buttons' => [
					'delete' => function ($url, $city, $key) use ($model) {
						return Html::a('Удалить', ['city-remove', 'id' => $model->ot_id, 'city' => $city->id], [
							'data' => [
								'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
								'method' => 'post',
							]]);
					},
				],
			],

		],
	]); ?>
<?php endif; ?>