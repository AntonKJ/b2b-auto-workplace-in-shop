<?php

namespace api\components;

use yii\base\Component;
use yii\helpers\Inflector;
use yii\web\BadRequestHttpException;
use yii\web\RequestParserInterface;

class XmlParser extends Component implements RequestParserInterface
{

	/**
	 * Whether throw the [[BadRequestHttpException]] if the process error.
	 * @var boolean
	 */
	public $throwException = true;

	/**
	 * @param $rawBody
	 * @param $contentType
	 * @return array|mixed|\SimpleXMLElement
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function parse($rawBody, $contentType)
	{

		libxml_use_internal_errors(true);

		$result = simplexml_load_string($rawBody, 'SimpleXMLElement', LIBXML_NOCDATA);

		if ($result === false) {

			$errors = libxml_get_errors();
			$latestError = array_pop($errors);
			$error = [
				'message' => $latestError->message,
				'type' => $latestError->level,
				'code' => $latestError->code,
				'file' => $latestError->file,
				'line' => $latestError->line,
			];

			if ($this->throwException) {
				throw new BadRequestHttpException($latestError->message);
			}

			return $error;
		}

		$result = json_decode(json_encode($result), true);
		if (\is_array($result))
			$result = $this->prepareKeys($result);

		return $result;
	}

	/**
	 * @param array $params
	 * @return array
	 */
	protected function prepareKeys(array $params)
	{

		$prepareKeys = function ($items) use (&$prepareKeys) {

			$out = [];

			/** @var array $items */
			foreach ($items as $key => $value) {

				if (!is_numeric($key))
					$key = Inflector::variablize($key);

				if (\is_array($value))
					$value = $prepareKeys($value);

				$out[$key] = $value;
			}

			return $out;
		};

		return $prepareKeys($params);
	}

}