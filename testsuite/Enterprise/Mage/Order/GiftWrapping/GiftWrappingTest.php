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
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tests for invoice, shipment and credit memo with gift options
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Enterprise_Mage_Order_GiftWrapping_GiftWrappingTest extends Mage_Selenium_TestCase
{
    public function assertPreconditions()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Creating 2 simple products</p>
     *
     * @test
     * @return array
     */
    public function preconditionsCreateProducts()
    {
        $this->navigate('manage_products');
        $product1 = $this->loadDataSet('Product', 'simple_product_visible');
        $product2 = $this->loadDataSet('Product', 'simple_product_visible');
        $this->productHelper()->createProduct($product1);
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->createProduct($product2);
        $this->assertMessagePresent('success', 'success_saved_product');
        return array($product1, $product2);
    }

    /**
     * <p>Create Gift Wrapping for tests</p>
     *
     * @test
     * @return array $giftWrappingData
     */
    public function preconditionsCreateGiftWrapping()
    {
        $giftWrappingData = $this->loadDataSet('GiftWrapping', 'gift_wrapping_without_image');
        $this->navigate('manage_gift_wrapping');
        $this->giftWrappingHelper()->createGiftWrapping($giftWrappingData);
        $this->assertMessagePresent('success', 'success_saved_gift_wrapping');
        return $giftWrappingData;
    }

    /**
     * <p>Create Customer</p>
     *
     * @test
     * @return array $userData
     */
    public function preconditionsCreateCustomer()
    {
        $userData = $this->loadDataSet('Customers', 'customer_account_register');
        $this->logoutCustomer();
        $this->frontend('customer_login');
        $this->customerHelper()->registerCustomer($userData);
        $this->assertMessagePresent('success', 'success_registration');
        return array('email' => $userData['email'], 'password' => $userData['password']);
    }

    /**
     * <p>Test Case TL-MAGE-971: Correct displaying Gift Options for Order</p>
     * <p>Preconditions:</p>
     * <p>1. Simple product is created;</p>
     * <p>2. Customer is created;</p>
     * <p>3. Gift wrapping is created;</p>
     * <p>4. Gift wrapping and gift messages are allowed for entire order and individual items in system configuration.</p>
     * <p>Steps:</p>
     * <p>1. Log into Frontend;</p>
     * <p>2. Add one product to shopping cart;</p>
     * <p>3. Click on "Checkout with Multiple Addresses" link;</p>
     * <p>4. Select a different shipping address for item;</p>
     * <p>5. Click button "Continue to shipping information";</p>
     * <p>6. Select shipping method for item "Flat Rate";</p>
     * <p>7. Check the "Add gift options" checkbox;</p>
     * <p>8. Check the "Add Gift Options for Entire Order" checkbox;</p>
     * <p>9. Select Gift Wrapping from "Gift Wrapping Design" dropdown;</p>
     * <p>10. Click "Gift Message" link for entire order;</p>
     * <p>11. Add gift message for entire order;</p>
     * <p>12. Check the "Add gift options for Individual Items" checkbox in the second item.
     * <p>13. Select Gift Wrapping from "Gift Wrapping Design" dropdown for item;</p>
     * <p>14. Click "Gift Message" link for individual item;</p>
     * <p>15. Add gift message for individual item;<p>
     * <p>16. Add gift card for order;</p>
     * <p>17. Send gift receipt;</p>
     * <p>18. Proceed to billing information page;</p>
     * <p>19. Select payment method "Check/Money Order";</p>
     * <p>20. Proceed to review order information;</p>
     * <p>21. Check presence of gift wrapping for item and entire order in totals;</p>
     * <p>22. Submit order;</p>
     * <p>23. Navigate to sales - orders on the backend;</p>
     * <p>24. Open newly created order;</p>
     * <p>25. Send email with order info to customer by pressing button "Send email";</p>
     * <p>26. Open email with order information.</p>
     * <p>Expected Results:</p>
     * <p>1. Email contains gift options.</p>
     *
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProducts
     * @param array $customerData
     * @param array $productData
     * @return array $orderId
     *
     * @test
     */
    public function createOrder($customerData, $productData)
    {
        //Preconditions
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('gift_wrapping_all_enable');
        $this->systemConfigurationHelper()->configure('gift_message_all_enable');
        //Data
        $giftWrappingData = $this->loadDataSet('MultipleAddressesCheckout', 'gift_wrapping_without_image');
        $individualItemsMessage = $this->loadDataSet('MultipleAddressesCheckout', 'gift_message_for_individual_items');
        $indItems = array($productData['general_name'] =>
                                        array('item_gift_wrapping_design' => $giftWrappingData['gift_wrapping_design'],
                                              'gift_message' => $individualItemsMessage));
        $checkoutData = $this->loadDataSet('MultipleAddressesCheckout', 'multiple_with_gift_wrapping_message_card_receipt',
                                array('email' => $customerData['email'], 'password' => $customerData['password'],
                                'order_gift_wrapping_design' => $giftWrappingData['gift_wrapping_design'],
                                'individual_items' => $indItems),
                                array('product_1' => $productData[0]['general_name'],
                                      'validate_name_1' => $productData[0]['general_name'] .
                                        ' Gift Wrapping Design : ' . $giftWrappingData['gift_wrapping_design']));
        //Steps
        $this->navigate('manage_gift_wrapping');
        $this->giftWrappingHelper()->createGiftWrapping($giftWrappingData);
        $this->assertMessagePresent('success', 'success_saved_gift_wrapping');
        $this->customerHelper()->frontLoginCustomer($customerData);
        $this->shoppingCartHelper()->frontClearShoppingCart();
        $this->logoutCustomer();
        $orderId = $this->checkoutMultipleAddressesHelper()->frontMultipleCheckout($checkoutData);
        //Verification
        $this->assertMessagePresent('success', 'success_checkout');
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_sales_orders');
        $this->addParameter('order_id', '#' . $orderId[0]);
        $this->searchAndOpen(array('filter_order_id' => $orderId[0]));
        $this->clickButtonAndConfirm('send_email', 'confirmation_to_send_email');
        //Verification
        //TODO: Implement email verification for completing test

        return $orderId;
    }

    /**
     * <p>Test Case TL-MAGE-955: Correct displaying Gift Options for Invoice</p>
     * <p>Preconditions:</p>
     * <p>1. Navigate to sales - orders on the backend;</p>
     * <p>2. Submit invoice with "Email copy of invoice" option in invoice totals;</p>
     * <p>3. Open newly submitted invoice in sales - invoices;</p>
     * <p>4. Open email with invoice information.</p>
     * <p>Expected Results:</p>
     * <p>1. Email contains gift options.</p>
     *
     * @depends createOrder
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProducts
     * @param array $orderId
     *
     * @test
     */
    public function orderWithInvoice($orderId)
    {
        //Steps
        $this->navigate('manage_sales_orders');
        $this->addParameter('order_id', '#' . $orderId[0]);
        $this->searchAndOpen(array('filter_order_id' => $orderId[0]));
        $this->clickButton('invoice');
        $this->fillForm(array('email_copy_of_invoice' => 'Yes'));
        $this->clickButton('submit_invoice');
        //Verification
        //TODO: Implement email verification for completing test
    }

    /**
     * <p>Test Case TL-MAGE-958: Correct displaying Gift Options for Shipment</p>
     * <p>Preconditions:</p>
     * <p>1. Previous test succeeded;</p>
     * <p>Steps:</p>
     * <p>1. Navigate to sales - orders on the backend;</p>
     * <p>2. Open newly submitted order in sales - orders;</p>
     * <p>3. Submit shipment;</p>
     * <p>4. Open email with shipment information.</p>
     * <p>Expected Results:</p>
     * <p>1. Email contains gift options.</p>
     *
     * @depends createOrder
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProducts
     * @param array $orderId
     *
     * @test
     */
    public function orderWithShipment($orderId)
    {
        //Steps
        $this->navigate('manage_sales_orders');
        $this->addParameter('order_id', '#' . $orderId[0]);
        $this->searchAndOpen(array('filter_order_id' => $orderId[0]));
        $this->clickButton('ship');
        $this->fillForm(array('email_copy_of_shipment' => 'Yes'));
        $this->clickButton('submit_shipment');
        //Verification
        //TODO: Implement email verification for completing test
    }

    /**
     * <p>Test Case TL-MAGE-959: Correct displaying Gift Options for Credit Memo</p>
     * <p>Preconditions:</p>
     * <p>1. Previous test succeeded;</p>
     * <p>Steps:</p>
     * <p>1. Navigate to sales - orders on the backend;</p>
     * <p>2. Open newly submitted order in sales - orders;</p>
     * <p>3. Submit credit memo;</p>
     * <p>4. Open email with shipment information.</p>
     * <p>Expected Results:</p>
     * <p>1. Email contains gift options.</p>
     *
     * @depends createOrder
     * @depends preconditionsCreateCustomer
     * @depends preconditionsCreateProducts
     * @param array $orderId
     *
     * @test
     */
    public function orderWithCreditMemo($orderId)
    {
        //Steps
        $this->navigate('manage_sales_orders');
        $this->addParameter('order_id', '#' . $orderId[0]);
        $this->searchAndOpen(array('filter_order_id' => $orderId[0]));
        $this->clickButton('credit_memo');
        $this->fillForm(array('email_copy_of_credit_memo' => 'Yes'));
        $this->clickButton('refund_offline');
        //Verification
        //TODO: Implement email verification for completing test
    }

   /**
    * <p>Preconditions:</p>
    * <p>System -> Sales -> Gift Options (Default scope) -> Switch to "no" following options:</p>
    * <p>"Allow Gift Messages on Order Level";</p>
    * <p>"Allow Gift Messages for Order Items";</p>
    * <p>"Allow Gift Wrapping on Order Level";</p>
    * <p>"Allow Gift Wrapping for Order Items";</p>
    * <p>"Allow Gift Receipt";</p>
    * <p>"Allow Printed Card";</p>

    * <p>System -> Sales -> Gift Options (Website scope) -> Switch to "yes" following options:</p>
    * <p>"Allow Gift Messages on Order Level";</p>
    * <p>"Allow Gift Messages for Order Items";</p>
    * <p>"Allow Gift Wrapping on Order Level";</p>
    * <p>"Allow Gift Wrapping for Order Items";<p>
    * <p>"Allow Gift Receipt";</p>
    * <p>"Allow Printed Card";</p>
    *
    * <p>Steps:</p>
    * <p>1. Log into beckend Sales-> Orders;</p>
    * <p>2. Push "create New Order";</p>
    * <p>3. Select any customer from list;</p>
    * <p>4. Select a Store from list;</p>
    * <p>5. Add at least 2 products uses "Add products" button;</p>
    * <p>6. Enter Billing and shipping addresses;</p>
    * <p>7. Choose Shipping and payment Methods;</p>
    * <p>8. Edit gift masseges for entire order and Items individually;</p>
    * <p>9. Push "Submit Order" button;</p>
    * <p>10.Make overview of recently created order and check if all switched in this test case gift options is</p>
    * <p> saved;</p>
    *
    * <p>Expected result:</p>
    * <p>After step 9: Should appears notification massage "The order has been created."</p>
    * <p>After step 10: All switched in this test case gift options is saved;</p>
    *
    * @TestlinkId TL-MAGE-861
    * @depends preconditionsCreateProducts
    * @depends preconditionsCreateGiftWrapping
    * @param $productData
    * @param $giftWrappingData
    * @test
    *
    */
    public function giftWrappingBackendWebsite($productData, $giftWrappingData)
    {
        //Preconditions
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('gift_options_disable_all');
        $this->systemConfigurationHelper()->configure('gift_options_enable_all_website');
        //Data
        $orderData = $this->loadDataSet('SalesOrder', 'order_gift_options_full',
            array('gift_wrapping_design' => $giftWrappingData['gift_wrapping_design']));
        $orderData['products_to_add']['product_1']['filter_sku'] = $productData[0]['general_sku'];
        $orderData['products_to_add']['product_2']['filter_sku'] = $productData[1]['general_sku'];
        $orderData['gift_messages']['individual_item']['product_1']['sku_product'] = $productData[0]['general_sku'];
        $orderData['gift_messages']['individual_item']['product_2']['sku_product'] = $productData[1]['general_sku'];
        //Steps
        $this->navigate('manage_sales_orders');
        $this->orderHelper()->createOrder($orderData, FALSE);
        //Verification
        $this->assertMessagePresent('success', 'success_created_order');
        $this->orderHelper()->verifyGiftOptions($orderData);
    }

    /**
     * <p>Preconditions:</p>
     * <p>System -> Sales -> Gift Options (Default scope) -> Switch to "yes" following options:</p>
     * <p>"Allow Gift Messages on Order Level";</p>
     * <p>"Allow Gift Messages for Order Items";</p>
     * <p>"Allow Gift Wrapping on Order Level";</p>
     * <p>"Allow Gift Wrapping for Order Items";</p>
     * <p>"Allow Gift Receipt";</p>
     * <p>"Allow Printed Card";</p>

     * <p>System -> Sales -> Gift Options (Website scope) -> Switch to "no" following options:</p>
     * <p>"Allow Gift Messages on Order Level";</p>
     * <p>"Allow Gift Messages for Order Items";</p>
     * <p>"Allow Gift Wrapping on Order Level";</p>
     * <p>"Allow Gift Wrapping for Order Items";<p>
     * <p>"Allow Gift Receipt";</p>
     * <p>"Allow Printed Card";</p>
     *
     * <p>Steps:</p>
     * <p>1. Log into beckend Sales-> Orders;</p>
     * <p>2. Push "create New Order";</p>
     * <p>3. Select any customer from list;</p>
     * <p>4. Select a Store from list;</p>
     * <p>5. Add at least 2 products uses "Add products" button;</p>
     *
     * <p>Expected result:</p>
     * <p>After step 5: Should not appears "Gift Options" link under the added product;</p>
     *
     * @TestlinkId TL-MAGE-872
     * @depends preconditionsCreateProducts
     * @param $productData
     * @test
     *
     */
    public function giftWrappingBackendGlobalScope($productData)
    {
        //Preconditions
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('gift_options_enable_all_default_config');
        $this->systemConfigurationHelper()->configure('gift_options_disable_all_website');
        //Data
        $orderData = $this->loadDataSet('SalesOrder', 'order_gift_options_full');
        $orderData['products_to_add']['product_1']['filter_sku'] = $productData[0]['general_sku'];
        $orderData['products_to_add']['product_2']['filter_sku'] = $productData[1]['general_sku'];
        //Steps
        $this->navigate('manage_sales_orders');

        $this->orderHelper()->navigateToCreateOrderPage(null, $orderData['store_view']);
        $this->fillForm($orderData['account_data']);
        foreach ($orderData['products_to_add'] as $value) {
            $this->orderHelper()->addProductToOrder($value);
        }
        //Verification
        $this->orderHelper()->verifyGiftOptionsDisabled($orderData);
    }
}
