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
 * @category   Mage
 * @package    Mage_CatalogInventory
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Stock model
 *
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_CatalogInventory_Model_Stock extends Mage_Core_Model_Abstract
{
    const BACKORDERS_NO     = 0;
    const BACKORDERS_BELOW  = 1;
    const BACKORDERS_YES    = 2;

    protected function _construct()
    {
        $this->_init('cataloginventory/stock');
    }

    /**
     * Retrieve stock identifier
     *
     * @return int
     */
    public function getId()
    {
        return 1;
    }

    /**
     * Add stock item objects to products
     *
     * @param   collection $products
     * @return  Mage_CatalogInventory_Model_Stock
     */
    public function addItemsToProducts($productCollection)
    {
        $items = $this->getItemCollection()
            ->addProductsFilter($productCollection)
            ->load();
        foreach ($items as $item) {
            foreach($productCollection as $product){
                if($product->getId()==$item->getProductId()){
                    if($product instanceof Mage_Catalog_Model_Product) {
                    	$item->assignProduct($product);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Retrieve items collection object with stock filter
     *
     * @return unknown
     */
    public function getItemCollection()
    {
        return Mage::getResourceModel('cataloginventory/stock_item_collection')
            ->addStockFilter($this->getId());
    }

    /**
     * Subtract ordered qty for product
     *
     * @param   Varien_Object $item
     * @return  Mage_CatalogInventory_Model_Stock
     */
    public function registerItemSale(Varien_Object $item)
    {
        if ($productId = $item->getProductId()) {
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
            if ($item->getStoreId()) {
                $stockItem->setStoreId($item->getStoreId());
            }
            if ($stockItem->checkQty($item->getQtyOrdered())) {
                $stockItem->subtractQty($item->getQtyOrdered())
                          ->save();
            }
        }
        else {
            Mage::throwException(Mage::helper('cataloginventory')->__('Can not specify product identifier for order item'));
        }
        return $this;
    }

    /**
     * Back stock item data when we cancel order items
     *
     * @param Varien_Object $item
     */
    public function cancelItemSale(Varien_Object $item)
    {
        if (($productId = $item->getProductId()) && ($qty = $item->getQtyToShip())) {
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
            if ($item->getStoreId()) {
                $stockItem->setStoreId($item->getStoreId());
            }
            $stockItem->addQty($qty)
                ->save();
        }
        return $this;
    }

    /**
     * Lock stock items for product ids array
     *
     * @param   array $productIds
     * @return  Mage_CatalogInventory_Model_Stock
     */
    public function lockProductItems($productIds)
    {
        $this->_getResource()->lockProductItems($this, $productIds);
        return $this;
    }
}
