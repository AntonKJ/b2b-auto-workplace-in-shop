<?php

namespace api\tests\functional\nokian;

use api\tests\FunctionalTester;

/**
 * Class LoginCest
 */
class StoreCest
{
	public function _before(FunctionalTester $I)
	{
	}

	/**
	 * @param FunctionalTester $I
	 */
	public function check(FunctionalTester $I)
	{
		$I->wantToTest('Check availability by goods');

		$data = '<?xml version="1.0" encoding="UTF-8"?>
<request>
    <entity>STORE</entity>
    <action>CHECK</action>
    <shop-id>Vianor_296</shop-id>
    <product>
        <code>T430120</code>
    </product>
</request>';

		$I->amHttpAuthenticated('vianor_shin', '5XjOV6GiWGxD');
		$I->sendPOST('/nokian/store/check', $data);
		$I->seeResponseCodeIsSuccessful();
		$I->seeResponseIsXml();
		$I->seeXmlResponseMatchesXpath('//response/product/code');
		$I->seeXmlResponseMatchesXpath('//response/product/quantity');
	}
}
