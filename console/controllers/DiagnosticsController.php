<?php

namespace console\controllers;

use console\controllers\diagnostics\RawAvailabilityAction;
use console\controllers\diagnostics\RealAvailabilityAction;
use console\controllers\diagnostics\UserOrderTypeGroupAction;
use yii\console\Controller;

class DiagnosticsController extends Controller
{

	public function actions()
	{

		$actions = parent::actions();
		$actions['raw-availability'] = RawAvailabilityAction::class;
		$actions['real-availability'] = RealAvailabilityAction::class;
		$actions['user-info'] = UserOrderTypeGroupAction::class;

		return $actions;
	}

}
