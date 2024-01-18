<?php

/* @var $this yii\web\View */
/* @var $model common\models\DiskBrand */

$this->title = 'Бренд: ' . $model->name;

$this->params['breadcrumbs'][] = ['label' => 'Диски', 'url' => ['index']];
$this->params['breadcrumbs'][] = "Бренд: {$model->name}";
?>

<p><?php echo \yii\helpers\Html::a('Модели', ['disk-model/index', 'brand_id' => $model->id]) ?></p>

<?php echo \yii\widgets\DetailView::widget([
	'model' => $model,
]) ?>

<?php var_dump($model->attributes); ?>
