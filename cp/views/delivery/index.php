<?php

use common\models\Region;

/* @var $this yii\web\View */
/* @var $region Region */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = sprintf("Диагностика доставок для региона '%s' [%d]", $region->getTitle(), $region->getId());
$this->params['breadcrumbs'][] = $this->title;
?>
