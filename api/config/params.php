<?php

return [
	'apiConfig' => [
		'endPointUrl' => '/',
	],
	'userRepository' => require __DIR__ . '/vendor-users.php',
	'soap' => [
		'wsdl' => 'http://77.233.99.26/ut/ws/ExchangeOfSite/ExchangeOfSite.1cws?wsdl',
		'username' => 'WebObmen',
		'password' => 'nembObeW',
	],
];
