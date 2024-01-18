<?php

namespace domain\entities\service1c;

class OrderReserve extends \domain\entities\EntityBase
{

	public $id;
	public $invoice;

	/**
	 * @var OrderInvoice|null
	 */
	public $invoice_entity;

	public $invoice_content;

	public $bik;
	public $bik_state;
	public $did; // delivery id
	public $cid; // client id ???
	public $cid_state; // client status ???
	public $shop;
	public $shop_state;
	public $ccpay_id;
	public $items;
	public $errors;
	public $response_raw;

	/**
	 * @return mixed
	 */
	public function getInvoiceContent()
	{
		return $this->invoice_content;
	}

	/**
	 * @param mixed $invoice_content
	 * @return OrderReserve
	 */
	public function setInvoiceContent($invoice_content)
	{
		$this->invoice_content = $invoice_content;
		return $this;
	}

	/**
	 * @return OrderInvoice
	 */
	public function getInvoiceEntity()
	{
		return $this->invoice_entity;
	}

	/**
	 * @param OrderInvoice|null $invoice_entity
	 * @return OrderReserve
	 */
	public function setInvoiceEntity($invoice_entity)
	{
		$this->invoice_entity = $invoice_entity;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 * @return OrderReserve
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getInvoice()
	{
		return $this->invoice;
	}

	/**
	 * @param mixed $invoice
	 * @return OrderReserve
	 */
	public function setInvoice($invoice)
	{
		$this->invoice = $invoice;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDid()
	{
		return $this->did;
	}

	/**
	 * @param mixed $did
	 * @return OrderReserve
	 */
	public function setDid($did)
	{
		$this->did = $did;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCid()
	{
		return $this->cid;
	}

	/**
	 * @param mixed $cid
	 * @return OrderReserve
	 */
	public function setCid($cid)
	{
		$this->cid = $cid;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCidState()
	{
		return $this->cid_state;
	}

	/**
	 * @param mixed $cid_state
	 * @return OrderReserve
	 */
	public function setCidState($cid_state)
	{
		$this->cid_state = $cid_state;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getShop()
	{
		return $this->shop;
	}

	/**
	 * @param mixed $shop
	 * @return OrderReserve
	 */
	public function setShop($shop)
	{
		$this->shop = $shop;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getShopState()
	{
		return $this->shop_state;
	}

	/**
	 * @param mixed $shop_state
	 * @return OrderReserve
	 */
	public function setShopState($shop_state)
	{
		$this->shop_state = $shop_state;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBikState()
	{
		return $this->bik_state;
	}

	/**
	 * @param mixed $bik_state
	 * @return OrderReserve
	 */
	public function setBikState($bik_state)
	{
		$this->bik_state = $bik_state;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCcpayId()
	{
		return $this->ccpay_id;
	}

	/**
	 * @param mixed $ccpay_id
	 * @return OrderReserve
	 */
	public function setCcpayId($ccpay_id)
	{
		$this->ccpay_id = $ccpay_id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @param mixed $items
	 * @return OrderReserve
	 */
	public function setItems($items)
	{
		$this->items = $items;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * @param mixed $errors
	 * @return OrderReserve
	 */
	public function setErrors($errors)
	{
		$this->errors = $errors;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getResponseRaw()
	{
		return $this->response_raw;
	}

	/**
	 * @param mixed $response_raw
	 * @return OrderReserve
	 */
	public function setResponseRaw($response_raw)
	{
		$this->response_raw = $response_raw;
		return $this;
	}


	public function fields()
	{
		return [
			'id',
			'did',
			'invoice',
			'invoice_content',
//			'cid',
//			'cid_state',
//			'shop',
//			'shop_state',
//			'bik',
//			'bik_state',
			'ccpay_id',
//			'items',
			'errors',
		];
	}

}