<?php

namespace console\controllers;

use console\controllers\import\DisksAction;
use yii\console\Controller;

class UtilsController extends Controller
{

	public function actions()
	{
		$actions = parent::actions();

		$actions['disks'] = DisksAction::class;

		return $actions;
	}

}