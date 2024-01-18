<?php

/* @var $this \yii\web\View */

/* @var $content string */

use cp\assets\AppAsset;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Breadcrumbs;

AppAsset::register($this);

if (isset($this->params['config']))
	$this->registerJs('var appConfig = ' . \yii\helpers\Json::encode($this->params['config']) . ';', View::POS_HEAD);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>

	<meta charset="<?= Yii::$app->charset ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?= Html::csrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?></title>
	<?php $this->head() ?>

</head>
<body>
<?php $this->beginBody() ?>

<div class="container-fluid">
	<?php echo Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]); ?>
	<h1 class="page-header"><?php echo $this->title; ?></h1>
	<?= $content; ?>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
