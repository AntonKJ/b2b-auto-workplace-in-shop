<?php

namespace domain\traits;

use domain\interfaces\EventInterface;

trait EventTrait
{

	private $events = [];

	protected function recordEvent(EventInterface $event)
	{
		$this->events[] = $event;
	}

	public function releaseEvents()
	{
		$events = $this->events;
		$this->events = [];

		return $events;
	}
}