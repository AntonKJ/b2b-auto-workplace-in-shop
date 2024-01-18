<?php

use yii\web\Response;

return [
	'components' => [
		'request' => [
			// !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
			'cookieValidationKey' => '',
		],
		'response' => [
			'format' => Response::FORMAT_JSON,
			'on beforeSend' => function ($event) {
				$response = $event->sender;
				if ($response->data !== null) {
					if (!$response->isSuccessful) {
						$response->data = [
							'error' => [
								'message' => $response->data['message'],
							],
						];
					}
				}
			},
		],
	],
];
