<?php

use common\models\Region;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $this yii\web\View */

$this->title = 'Диагностика доставок';
$this->params['breadcrumbs'][] = $this->title;
?>

<form method="get">
	<label for="region_id">Регион</label>
	<?php echo Html::dropDownList(
		'region_id',
		null,
		ArrayHelper::map(Region::find()->defaultOrder()->all(), 'id', 'title'),
		[
			'prompt' => '~ Выберите регион',
			'id' => 'region_id',
		]
	); ?>
	<button type="submit">Выбрать регион</button>
</form>
