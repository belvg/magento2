<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

define('COUNT_STOCK_ITEMS_LIST', 3);

/* @var $product Mage_Catalog_Model_Product */
$productFixture = require '_fixture/_block/Catalog/Product.php';

/* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
$stockItemFixture = require '_fixture/_block/CatalogInventory/Stock/Item.php';

$products = array();
$stockItems = array();
for ($i = 0; $i < COUNT_STOCK_ITEMS_LIST; $i++) {
    $product = clone $productFixture;
    $products[] = $product->setSku('simple-product-' . microtime())->save();

    $stockItem = clone $stockItemFixture;
    $stockItems[] = $stockItem->setProductId($product->getId())->save();
}

Magento_Test_TestCase_ApiAbstract::setFixture('cataloginventory_stock_products', $products);
Magento_Test_TestCase_ApiAbstract::setFixture('cataloginventory_stock_items', $stockItems);
