<?php

namespace common\components;


class Html extends \yii\helpers\Html
{

	/**
	 * @param string $text
	 * @param null $email
	 * @param array $options
	 * @param bool $encode
	 * @return string
	 */
	public static function mailto($text, $email = null, $options = [], $encode = false)
	{

		$string = parent::mailto($text, $email, $options);

		if ($encode) {

			$string = "document.write('{$string}')";

			$js_encode = '';
			for ($x = 0, $l = mb_strlen($string); $x < $l; $x++)
				$js_encode .= '%' . bin2hex($string[$x]);

			$string = Html::script("eval(unescape('{$js_encode}'))");
		}

		return $string;
	}

}