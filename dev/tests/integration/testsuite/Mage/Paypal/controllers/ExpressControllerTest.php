<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Paypal
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * @group module:Mage_Paypal
 */
class Mage_Paypal_ExpressControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    /**
     * @magentoDataFixture Mage/Sales/_files/quote.php
     * @magentoDataFixture Mage/Paypal/_files/quote_payment.php
     */
    public function testReviewAction()
    {
        $quote = new Mage_Sales_Model_Quote();
        $quote->load('test01', 'reserved_order_id');
        Mage::getSingleton('Mage_Checkout_Model_Session')->setQuoteId($quote->getId());

        $this->dispatch('paypal/express/review');
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $html = $this->getResponse()->getBody();
        $this->assertContains('Simple Product', $html);
    }
}