<?php
/**
 * {license_notice}
 *
 * @category    Paas
 * @package     tests
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

//Add customer
$tagFixture = simplexml_load_file(__DIR__ . '/_data/xml/TagCRUD.xml');
$customerData = Magento_Test_Webservice::simpleXmlToArray($tagFixture->customer);
$customerData['email'] = mt_rand(1000, 9999) . '.' . $customerData['email'];

$customer = Mage::getModel('Mage_Customer_Model_Customer');
$customer->setData($customerData)->save();
Magento_Test_Webservice::setFixture('customerData', $customer, Magento_Test_Webservice::AUTO_TEAR_DOWN_DISABLED);

//Create new simple product
$productData = Magento_Test_Webservice::simpleXmlToArray($tagFixture->product);
$productData['sku'] = $productData['sku'] . mt_rand(1000, 9999);
$productData['name'] = $productData['name'] . ' ' . mt_rand(1000, 9999);

$product = Mage::getModel('Mage_Catalog_Model_Product');
$product->setData($productData)->save();
Magento_Test_Webservice::setFixture('productData', $product, Magento_Test_Webservice::AUTO_TEAR_DOWN_DISABLED);
