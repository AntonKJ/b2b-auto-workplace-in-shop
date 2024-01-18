<?php
return [
    'bootstrap' => ['gii'],
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
	'components' => [
		'sphinxConfig' => [
			'tsvPipeCommand' => '/app/yii sphinx/query',
		],
	],
];
