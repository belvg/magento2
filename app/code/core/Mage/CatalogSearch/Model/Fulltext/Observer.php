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
 * @category   Mage
 * @package    Mage_CatalogSearch
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * CatalogSearch Fulltext Observer
 *
 * @category    Mage
 * @package     Mage_CatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_CatalogSearch_Model_Fulltext_Observer
{
    /**
     * Update product index when product data updated
     *
     * @param Varien_Object $observer
     * @return Mage_CatalogSearch_Model_Fulltext_Observer
     */
    public function refreshProductIndex($observer)
    {
        $product = $observer->getEvent()->getProduct();

        Mage::getResourceModel('catalogsearch/fulltext')
            ->rebuildIndex($product->getStoreId(), $product->getId());

        return $this;
    }

    /**
     * Clean product index when product deleted or marked as unsearchable/invisible
     *
     * @param Varien_Object $observer
     * @return Mage_CatalogSearch_Model_Fulltext_Observer
     */
    public function cleanProductIndex($observer)
    {
        $product = $observer->getEvent()->getProduct();

        Mage::getResourceModel('catalogsearch/fulltext')
            ->cleanIndex(null, $product->getId());

        return $this;
    }

    /**
     * Update all attribute-dependant index
     *
     * @param Varien_Object $observer
     * @return Mage_CatalogSearch_Model_Fulltext_Observer
     */
    public function refreshIndexByAttribute($observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        return $this;
    }

    /**
     * Rebuild index after import
     *
     * @param Varien_Object $observer
     * @return Mage_CatalogSearch_Model_Fulltext_Observer
     */
    public function refreshIndexAfterImport($observer)
    {
        return $this;
    }
}