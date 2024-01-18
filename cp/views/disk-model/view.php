<?php

/* @var $this yii\web\View */
/* @var $model common\models\DiskBrand */

$this->title = 'Бренд: ' . $model->name;

$this->params['breadcrumbs'][] = ['label' => 'Диски', 'url' => ['index']];
$this->params['breadcrumbs'][] = "Бренд: {$model->name}";
?>

<?php echo \yii\widgets\DetailView::widget([
	'model' => $model,
]) ?>

<?php var_dump($model->attributes); ?>
