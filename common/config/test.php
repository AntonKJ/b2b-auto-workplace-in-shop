<?php

use common\components\User;
use common\models\OptUser;

return [
	'id' => 'app-common-tests',
	'basePath' => dirname(__DIR__),
	'components' => [
		'user' => [
			'class' => User::class,
			'identityClass' => OptUser::class,
		],
	],
];
