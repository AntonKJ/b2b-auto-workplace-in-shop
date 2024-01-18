<?php

use yii\rest\UrlRule;

return [

	'' => 'site/index',

	new UrlRule([
		'suffix' => '.json',
		'pluralize' => false,
		'controller' => [
			'api/zones' => 'api-zones',
		],
		'only' => [
			'index',
		],
		'patterns' => [
			'GET,HEAD' => 'index',
		],
	]),

];
