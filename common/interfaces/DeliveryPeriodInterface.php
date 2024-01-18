<?php

namespace common\interfaces;

interface DeliveryPeriodInterface
{

	public function getDateMin(): \DateTime;

	public function getDateMax(): \DateTime;

}