<?php
/**
 * This file is part of OXID eSales PayPal module.
 *
 * OXID eSales PayPal module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales PayPal module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales PayPal module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2017
 */

namespace OxidEsales\PayPalModule\Tests\Unit\Model;

/**
 * Testing oxAccessRightException class.
 */
class OrderPaymentCommentListTest extends \OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     *  Setup: Prepare data - create need tables
     */
    protected function setUp()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_orderpaymentcomments`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_orderpayments`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_order`');
    }

    /**
     * Test case for oePayPalOrderPayment::oePayPalOrderPaymentList()
     * Gets PayPal Order Payment history list
     */
    public function testLoadOrderPayments()
    {
        $oComment = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $oComment->setDate('2013-02-03 12:12:12');
        $oComment->setComment('comment1');
        $oComment->setPaymentId(2);
        $oComment->save();

        $oComment = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $oComment->setDate('2013-02-03 12:12:12');
        $oComment->setComment('comment2');
        $oComment->setPaymentId(2);
        $oComment->save();

        $aComments = new \OxidEsales\PayPalModule\Model\OrderPaymentCommentList();
        $aComments->load(2);

        $this->assertEquals(2, count($aComments));

        $i = 1;
        foreach ($aComments as $oComment) {
            $this->assertEquals('comment' . $i++, $oComment->getComment());
        }
    }


    /**
     * Test case for oePayPalOrderPayment::hasPendingPayment()
     * Checks if list has pending payments
     */
    public function testAddComment()
    {
        $oList = new \OxidEsales\PayPalModule\Model\OrderPaymentCommentList();
        $oList->load('payment');

        $this->assertEquals(0, count($oList));

        $oComment = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $oComment->setPaymentId('payment');
        $oComment->save();

        $oList = new \OxidEsales\PayPalModule\Model\OrderPaymentCommentList();
        $oList->load('payment');

        $this->assertEquals(1, count($oList));

        $oComment = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $oComment->setComment('Comment');
        $oList->addComment($oComment);

        $oList = new \OxidEsales\PayPalModule\Model\OrderPaymentCommentList();
        $oList->load('payment');

        $this->assertEquals(2, count($oList));
    }
}