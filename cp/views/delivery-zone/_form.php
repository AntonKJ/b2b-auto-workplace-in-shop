<?php

use common\models\DeliveryCity;
use cp\models\DeliveryZone;
use cp\models\OrderTypeDeliveryCityForm;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model DeliveryZone */
/* @var $form yii\widgets\ActiveForm */
?>

<?php if ($model->orderType->getIsCategoryCity() || $model->orderType->getIsCategoryRegion()): ?>
	<hr>
	<h3>Города области доставки</h3>

	<?php
	$cityModel = new OrderTypeDeliveryCityForm();
	$form = ActiveForm::begin([
		'layout' => 'inline',
		'options' => [
			'style' => 'margin-bottom: 1em;',
		],
		'action' => ['city-add', 'id' => $model->getId()],
	]);
	?>

	<fieldset>

		<legend>Добавить город</legend>

		<?php echo $form->errorSummary($cityModel); ?>

		<?= $form->field($cityModel, 'city_id')
			->dropDownList(ArrayHelper::map(DeliveryCity::find()
				->andWhere(['not in', 'id', $model->getCities()->select('id')->column()])
				->defaultOrder()
				->all(), 'id', 'title'), [
				'prompt' => '~ Выберите город',
			]) ?>

		<div class="form-group">
			<?= Html::submitButton('Добавить город', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
		</div>

	</fieldset>

	<?php ActiveForm::end(); ?>

	<?= GridView::widget([
		'dataProvider' => new ActiveDataProvider([
			'query' => $model->getCities()->defaultOrder(),
			'sort' => false,
			'pagination' => false,
		]),
		'columns' => [

			'id',
			'title',

			[
				'class' => ActionColumn::class,
				'template' => '{delete}',
				'buttons' => [
					'delete' => static function ($url, $city, $key) use ($model) {
						return Html::a('Удалить', ['city-remove', 'id' => $model->getId(), 'city' => $city->id], [
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