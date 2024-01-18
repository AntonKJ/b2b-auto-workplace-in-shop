<?php

namespace domain\entities\service1c;

use Dompdf\Dompdf;
use Dompdf\Options;

class OrderInvoiceRendererPdf extends OrderInvoiceRendererBase
{

	public $docroot;
	public $xslTemplate;
	public $fontsPath;

	public $storeAddress;
	public $transport;
	public $buyerName;

	public function render()
	{

		$renderer = new OrderInvoiceRendererHtml($this->invoice);

		$renderer->docroot = $this->docroot;
		$renderer->xslTemplate = $this->xslTemplate;

		$renderer->storeAddress = $this->storeAddress;
		$renderer->transport = $this->transport;
		$renderer->buyerName = $this->buyerName;

		$htmlData = $renderer->render();

		$options = new Options();

		if(!empty($this->fontsPath))
			$options->setFontDir($this->fontsPath);

		$options->setIsHtml5ParserEnabled(true);

		$dompdf = new Dompdf($options);

		$dompdf->loadHtml($htmlData);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();

		$pdfData = $dompdf->output();

		return $pdfData;
	}

}