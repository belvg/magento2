<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Catalog
 * @subpackage  performance_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

function retrieveAttributeSetId()
{
    $productResource = Mage::getModel('Mage_Catalog_Model_Product');
    $entityType = $productResource->getResource()->getEntityType();

    $sets = Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection')
        ->setEntityTypeFilter($entityType->getId())
        ->load();

    foreach ($sets as $setInfo) {
        return $setInfo->getId();
    }
}

$product = new Mage_Catalog_Model_Product();
$product->setTypeId('simple')
    ->setId(1)
    ->setAttributeSetId(retrieveAttributeSetId())
    ->setWebsiteIds(array(1))
    ->setName('Product 1')
    ->setShortDescription('Product 1 Short Description')
    ->setWeight(1)
    ->setDescription('Product 1 Description')
    ->setSku('product_1')
    ->setPrice(10)
    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
    ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
    ->setTaxClassId(0)
    ->save()
;

$stockItem = new Mage_CatalogInventory_Model_Stock_Item();
$stockItem->setProductId($product->getId())
    ->setTypeId($product->getTypeId())
    ->setStockId(Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID)
    ->setIsInStock(1)
    ->setQty(10000)
    ->setUseConfigMinQty(1)
    ->setUseConfigBackorders(1)
    ->setUseConfigMinSaleQty(1)
    ->setUseConfigMaxSaleQty(1)
    ->setUseConfigNotifyStockQty(1)
    ->setUseConfigManageStock(1)
    ->setUseConfigQtyIncrements(1)
    ->setUseConfigEnableQtyInc(1)
    ->save()
;