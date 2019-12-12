<?php

namespace OxidEsales\PayPalModule\Tests\Codeception\Acceptance;

use Codeception\Example;
use Codeception\Util\Fixtures;
use OxidEsales\Codeception\Step\Basket;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\PayPalModule\Tests\Codeception\AcceptanceTester;
use OxidEsales\PayPalModule\Tests\Codeception\Page\PayPalLogin;
use \Codeception\Util\Locator;

/**
 * Class CheckoutRedirectCest
 *
 * @package OxidEsales\PayPalModule\Tests\Codeception\Acceptance
 */
class CheckoutRedirectCest
{
    /**
     * @param AcceptanceTester $I
     */
    public function _before(AcceptanceTester $I)
    {
        $I->clearShopCache();
        $I->haveInDatabase('oxobject2payment', Fixtures::get('paymentMethod'));
        $I->haveInDatabase('oxobject2payment', Fixtures::get('paymentCountry'));
        $I->setPayPalSettingsData();
    }

    /**
     * @group checkoutFrontend
     * @example { "setting": false, "expectedEndText": "MESSAGE_SUBMIT_BOTTOM" }
     * @example { "setting": true, "expectedEndText": "THANK_YOU_FOR_ORDER" }
     *
     * @param AcceptanceTester $I
     */
    public function checkRedirectOnCheckout(AcceptanceTester $I, Example $example)
    {
        $I->wantToTest('redirect to finalize order on successful PayPal checkout');
        $I->updateConfigInDatabase('blOEPayPalFinalizeOrderOnPayPal', $example['setting']);

        $basket = new Basket($I);

        $basketItem = [
            'id' => 'dc5ffdf380e15674b56dd562a7cb6aec',
            'title' => 'Kuyichi leather belt JEVER',
            'amount' => 4,
            'price' => '119,60 €'
        ];

        $basket->addProductToBasket($basketItem['id'], $basketItem['amount']);
        $I->openShop()->seeMiniBasketContains([$basketItem], $basketItem['price'], $basketItem['amount']);

        $I->openShop()->openMiniBasket();

        $paypalButton = Locator::find(
            'input',
            ['id' => 'paypalExpressCheckoutMiniBasketImage']
        );

        $I->waitForElementVisible($paypalButton, 5);
        $I->click($paypalButton);

        $paypalPage = new PaypalLogin($I);

        $paypalUserEmail = Fixtures::get('sBuyerLogin');
        $paypalUserPassword = Fixtures::get('sBuyerPassword');
        $paypalPage->loginAndCheckout($paypalUserEmail, $paypalUserPassword);

        $I->waitForText(Translator::translate($example['expectedEndText']), 10);
    }
}