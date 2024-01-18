<?php

namespace common\components\notify;

use Yii;
use yii\base\Component;

class Notify extends Component
{

	public $emailSender;

	/**
	 * @param string|array $to
	 * @param string $subject
	 * @param string $view
	 * @param array $params
	 * @param null|string|array $from
	 * @param array $attachments
	 * @return bool
	 */
	protected function sendEmail($to, $subject, $view, array $params = [], $from = null, array $attachments = [])
	{
		/** @var \yii\mail\BaseMailer $mailer */
		$mailer = Yii::$app->mailer;

		$mailer->htmlLayout = 'layouts/html';
		$mailer->textLayout = 'layouts/text';

		if (null !== $from && !empty($from)) {
			$this->emailSender = $from;
		}

		if ($this->emailSender === null) {
			$this->emailSender = Yii::$app->params['supportEmail'] ?? 'noreply@example.com';
		}

		$message = $mailer->compose([
			'html' => "{$view}_html",
			'text' => "{$view}_text",
		], $params)
			->setTo($to)
			->setFrom($this->emailSender)
			->setSubject($subject);

		if ([] !== $attachments) {
			foreach ($attachments as $attach) {

				switch (true) {

					case isset($attach['file']) && !empty($attach['file']):

						$message->attach($attach['file'], $attach['options'] ?? []);
						break;

					case isset($attach['content']) && !empty($attach['content']):

						$message->attachContent($attach['content'], $attach['options'] ?? []);
						break;
				}
			}
		}

		return $message->send();
	}

}
