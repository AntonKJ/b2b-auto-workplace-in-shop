<?php

namespace api\tests\functional\regular;

use api\tests\FunctionalTester;

/**
 * Class shopCast
 * @package api\tests\functional\regular
 */
class ShopCast
{
        public function _before(FunctionalTester $I)
        {
        }

        /**
         * @param FunctionalTester $I
         */
        public function check(FunctionalTester $I)
        {
                $I->wantToTest('Check method for get response shops list');

                $data = '';

                $I->amHttpAuthenticated('vianor_shin', '5XjOV6GiWGxD');
                $I->sendPOST('/regular/shops', $data);
                $I->seeResponseCodeIsSuccessful();
                $I->seeResponseIsJson();
        }
}