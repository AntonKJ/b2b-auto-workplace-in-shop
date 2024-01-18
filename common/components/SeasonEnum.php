<?php

namespace common\components;

class SeasonEnum
{

	const SUMMER = 's';
	const WINTER = 'w';

	static public function getOptions()
	{
		return [
			static::SUMMER => 'Лето',
			static::WINTER => 'Зима',
		];
	}

}