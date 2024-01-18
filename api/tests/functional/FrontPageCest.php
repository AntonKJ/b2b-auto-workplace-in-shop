<?php

namespace api\tests\functional;

use api\tests\FunctionalTester;

/**
 * Class LoginCest
 */
class FrontPageCest
{
	public function _before(FunctionalTester $I)
	{
		/*		$I->haveFixtures([
					'user' => [
						'class' => UserFixture::class,
						'dataFile' => codecept_data_dir() . 'login_data.php',
					],
				]);*/
	}

	/**
	 * @param FunctionalTester $I
	 */
	public function loadFrontPage(FunctionalTester $I)
	{
		$I->amOnPage('/');
		$I->see('Myexample API © ' . date('Y'));
	}
}
