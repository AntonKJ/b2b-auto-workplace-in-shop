<?php

namespace domain\entities\service1c;

use DOMDocument;
use XSLTProcessor;

class OrderInvoiceRendererHtml extends OrderInvoiceRendererBase
{

	public $docroot;
	public $xslTemplate;

	public $storeAddress;
	public $transport;
	public $buyerName;

	protected function encode($str)
	{
		return trim(str_replace('&', '&amp;', $str));
	}

	protected function getXml()
	{

		$dt = date('d.m.y');

		$items = [];

		/**
		 * @var OrderGood $good
		 */
		foreach ($this->invoice->getGoods() as $good) {

			$item = <<<ITEM
<item num="{$good->getId()}">
	<name>{$this->encode($good->getTitle())}</name>
	<measure>{$good->getUnitText()}</measure>
	<amount>{$good->getAmount()}</amount>
	<price>{$good->getPrice()}</price>
	<total>{$good->getPriceTotal()}</total>
</item>
ITEM;

			$items[] = $item;
		}

		$items = implode("\n", $items);

		$xml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>\n
<invoice num="{$this->invoice->getInvoiceNumber()}" from="{$dt}">\n
	<docroot>{$this->docroot}</docroot>\n
	<items>\n{$items}\n</items>\n
	<vat>{$this->encode($this->invoice->getSumNDS())}</vat>\n
	<total>{$this->encode($this->invoice->getSum())}</total>\n
	<total_text>{$this->encode($this->invoice->getSumInWords())}</total_text>\n
	<volume>{$this->encode($this->invoice->getVolume())}</volume>\n
	<supplier_company>{$this->encode($this->invoice->getOrganization())}</supplier_company>\n
	<supplier>{$this->encode($this->invoice->getContractor())}</supplier>\n
	<buyer>{$this->encode($this->invoice->getBuyer())}</buyer>\n
	<bik>{$this->encode($this->invoice->getBankBik())}</bik>\n
	<bank>{$this->encode($this->invoice->getBankName())}</bank>\n
	<bank_rs>{$this->encode($this->invoice->getSettlementAccount())}</bank_rs>\n
	<bank_ks>{$this->encode($this->invoice->getCorrespondentAccount())}</bank_ks>\n
	<invoice_number>{$this->encode($this->invoice->getAccountNumber())}</invoice_number>\n
	<store_address>{$this->encode($this->storeAddress)}</store_address>\n
	<transport>{$this->encode($this->transport)}</transport>\n
	<buyer_name>{$this->encode($this->buyerName)}</buyer_name>\n
</invoice>\n
XML;

		return $xml;
	}

	public function render()
	{

		$xml = $this->getXml();

		$xmlDoc = new DOMDocument(null, 'utf-8');
		$xmlDoc->loadXML($xml);

		$xslDoc = new DOMDocument;
		$xslDoc->load($this->xslTemplate);

		$proc = new XSLTProcessor();
		$proc->importStyleSheet($xslDoc);

		$html = $proc->transformToXml($xmlDoc);

		return $html;
	}

}