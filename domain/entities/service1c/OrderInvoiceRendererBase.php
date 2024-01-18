<?php

namespace domain\entities\service1c;

abstract class OrderInvoiceRendererBase implements OrderInvoiceRendererInterface
{

	/**
	 * @var OrderInvoice
	 */
	protected $invoice;

	/**
	 * OrderInvoiceRendererBase constructor.
	 */
	public function __construct(OrderInvoice $invoice)
	{
		$this->invoice = $invoice;
	}

	abstract public function render();

}