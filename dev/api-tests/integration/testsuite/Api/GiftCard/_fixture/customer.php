<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

$customer = Mage::getModel('Mage_Customer_Model_Customer');

$customer
    ->setStoreId(1)
    ->setCreatedIn('Default Store View')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setEmail('mr.test_giftcard' . uniqid() . '@test.com')
    ->setFirstname('Test')
    ->setLastname('Test')
    ->setMiddlename('Test')
    ->setGroupId(1)
    ->setRewardUpdateNotification(1)
    ->setRewardWarningNotification(1)
    ->save();
Magento_Test_Webservice::setFixture('giftcard/customer', $customer);
