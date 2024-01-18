<?php

namespace api\components;

class ResponseEventHandler
{
	public static function onBeforeSend($event)
	{
		$response = $event->sender;
		if (($response->data !== null) && !$response->isSuccessful && isset($response->data['message'])) {
			$response->data = [
				'error' => [
					'message' => $response->data['message'],
				],
			];
		}
	}
}
