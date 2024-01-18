<?php
return [
	'components' => [
		'db' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=localhost;dbname=myexample',
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
			'enableSchemaCache' => true,
		],
		'sphinx' => [
			'class' => 'yii\sphinx\Connection',
			'dsn' => 'mysql:host=127.0.0.1;port=9307;',
			'username' => '',
			'password' => '',
		],
		'webservice' => [
			'class' => 'common\components\webService\WebService',
			'wsdl' => '@common/config/webservice.wsdl',
			'username' => '',
			'password' => '',
		],
		'mailer' => [
			'class' => 'yii\swiftmailer\Mailer',
			'viewPath' => '@common/mail',
		],
	],
];
