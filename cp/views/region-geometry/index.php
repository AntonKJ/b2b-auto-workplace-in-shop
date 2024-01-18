<?php

/** @var yii\web\View $this */

\cp\assets\RegionGeometryAsset::register($this);

$this->title = 'Геометрия регионов';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="region-geometry-index" ng-app="app">
	<ui-view>
		<span>Загрузка...</span>
	</ui-view>
</div>
