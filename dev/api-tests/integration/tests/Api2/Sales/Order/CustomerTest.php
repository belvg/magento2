<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Magento
 * @package     Magento_Test
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magento.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test for order item API2
 *
 * @category    Magento
 * @package     Magento_Test
 * @author      Magento Api Team <api-team@magento.com>
 */
class Api2_Sales_Order_CustomerTest extends Magento_Test_Webservice_Rest_Customer
{
    /**
     * Delete fixtures
     */
    protected function tearDown()
    {
        Magento_Test_Webservice::deleteFixture('customer_order', true);
        Magento_Test_Webservice::deleteFixture('customer_quote', true);
        $fixtureProducts = $this->getFixture('customer_products');
        if ($fixtureProducts && count($fixtureProducts)) {
            foreach ($fixtureProducts as $fixtureProduct) {
                $this->callModelDelete($fixtureProduct, true);
            }
        }

        Magento_Test_Webservice::deleteFixture('order', true);
        Magento_Test_Webservice::deleteFixture('quote', true);
        $fixtureProducts = $this->getFixture('products');
        if ($fixtureProducts && count($fixtureProducts)) {
            foreach ($fixtureProducts as $fixtureProduct) {
                $this->callModelDelete($fixtureProduct, true);
            }
        }

        $fixtureOrders = $this->getFixture('orders_list_customer');
        if ($fixtureOrders && count($fixtureOrders)) {
            foreach ($fixtureOrders as $fixtureOrder) {
                $this->callModelDelete($fixtureOrder, true);
            }
        }

        parent::tearDown();
    }

    /**
     * Test get order item for customer
     *
     * @magentoDataFixture Api2/Sales/_fixtures/customer_order.php
     */
    public function testGetOrder()
    {
        /* @var $fixtureOrder Mage_Sales_Model_Order */
        $fixtureOrder = $this->getFixture('customer_order');
        $restResponse = $this->callGet('orders/' . $fixtureOrder->getId());
        $this->assertEquals(Mage_Api2_Model_Server::HTTP_OK, $restResponse->getStatus());

        $responseData = $restResponse->getBody();
        $this->assertNotEmpty($responseData);

        $fixtureOrderData = $fixtureOrder->getData(); // for total_due, base_total_due
        foreach ($responseData as $field => $value) {
            if (isset($fixtureOrderData[$field])) {
                $this->assertEquals($fixtureOrderData[$field], $value);
            }
        }
    }

    /**
     * Test retrieving not existing order item
     */
    public function testGetUnavailableOrder()
    {
        $restResponse = $this->callGet('orders/' . 'invalid_id');
        $this->assertEquals(Mage_Api2_Model_Server::HTTP_NOT_FOUND, $restResponse->getStatus());
    }

    /**
     * Test get order if customer is not owner
     *
     * @magentoDataFixture Api2/Sales/_fixtures/order.php
     */
    public function testGetOrderIfCustomerIsNotOwner()
    {
        /* @var $fixtureOrder Mage_Sales_Model_Order */
        $fixtureOrder = $this->getFixture('order');
        $restResponse = $this->callGet('orders/' . $fixtureOrder->getId());
        $this->assertEquals(Mage_Api2_Model_Server::HTTP_NOT_FOUND, $restResponse->getStatus());
    }

    /**
     * Test get orders for customer
     *
     * @magentoDataFixture Api2/Sales/_fixtures/orders_list_customer.php
     */
    public function testGetOrders()
    {
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId())->loadByEmail(TESTS_CUSTOMER_EMAIL);

        $restResponse = $this->callGet('orders', array('order' => 'entity_id', 'dir' => Zend_Db_Select::SQL_DESC));
        $this->assertEquals(Mage_Api2_Model_Server::HTTP_OK, $restResponse->getStatus());

        $orders = $restResponse->getBody();
        $this->assertNotEmpty($orders);
        $ordersIds = array();
        foreach ($orders as $order) {
            $ordersIds[] = $order['entity_id'];
        }

        $fixtureOrders = $this->getFixture('orders_list_customer');
        foreach ($fixtureOrders as $fixtureOrder) {
            if ($fixtureOrder->getCustomerId() == $customer->getId()) {
                $this->assertContains($fixtureOrder->getId(), $ordersIds,
                    'Order by current customer should be in response');
            } else {
                $this->assertNotContains($fixtureOrder->getId(), $ordersIds,
                    'Order by current customer should NOT be in response');
            }
        }
    }
}
