<?php

namespace common\components\notify;

use common\components\deliveries\DeliveryPickup;
use common\models\forms\B2BOrderForm;
use domain\entities\service1c\OrderInvoiceRendererPdf;
use domain\entities\service1c\OrderReserve;
use Yii;
use yii\helpers\FileHelper;
use yii\validators\EmailValidator;

class OrderNotify extends Notify
{

	/**
	 * @param B2BOrderForm $orderForm
	 * @param OrderReserve $reserve
	 * @return bool
	 * @throws \Exception
	 */
	public function orderCreate(B2BOrderForm $orderForm, OrderReserve $reserve)
	{

		$deliveryModel = $orderForm->getDeliveryModel();

		$recepients = Yii::$app->params['notify.email']['order.create'];
		$emailSubject = "Заказ {$reserve->getId()}. Ваш заказ в интернет-магазине Продажа шин.";

		// Если самовывоз
		if ($orderForm->deliveryType == DeliveryPickup::getCategory()) {

			$template = "order/createOrder_{$orderForm->deliveryType}";
		} else {

			if (!empty($email = $deliveryModel->getShop()->getEmail())) {
				$recepients[] = $email;
			}

			$emailSubject = "Доставка. Заказ {$reserve->getId()}. Ваш заказ в интернет-магазине Продажа шин.";

			// иначе доставка
			$template = 'order/createOrder_delivery';
		}

		$attachments = [];
		if (($invoice = $reserve->getInvoiceEntity()) !== null) {

			if ($orderForm->deliveryType != DeliveryPickup::getCategory()) {
				$recepients[] = 'B2B-Dostavka-site@myexample.ru';
			}

			$renderer = new OrderInvoiceRendererPdf($invoice);

			$renderer->docroot = Yii::getAlias('@storage/stamps');
			$renderer->xslTemplate = Yii::getAlias('@storage/xsl/invoice.xslt');
			$renderer->fontsPath = Yii::getAlias('@storage/fonts');

			$pdfData = $renderer->render();
			$date = date('Y-m-d');

			$attachments[] = [
				'content' => $pdfData,
				'options' => [
					'fileName' => "invoice_{$reserve->getId()}_{$date}.pdf",
					'contentType' => FileHelper::getMimeTypeByExtension('.pdf'),
				],
			];
		}

		$validator = new EmailValidator();

		$recepients[] = $orderForm->getOrder()->email;

		$recepients = array_filter($recepients, static function ($v, $k) use ($validator) {
			if (is_numeric($k)) {
				$k = $v;
			}
			return $validator->validate($k);
		}, ARRAY_FILTER_USE_BOTH);

		return $this->sendEmail($recepients, $emailSubject, $template, [
			'orderForm' => $orderForm,
			'reserve' => $reserve,
		], Yii::$app->params['notify.email']['send.from'], $attachments);
	}

}
