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
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Category collection
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Collection_Abstract
{
    protected $_productTable;
    protected $_productStoreId;
    protected $_productWebsiteTable;

    protected $_loadWithProductCount = false;

    protected function _construct()
    {
        $this->_init('catalog/category');

        $this->_productWebsiteTable = Mage::getSingleton('core/resource')->getTableName('catalog/product_website');
        $this->_productTable = Mage::getSingleton('core/resource')->getTableName('catalog/category_product');
    }

    public function addIdFilter($categoryIds)
    {
        if (is_array($categoryIds)) {
            if (empty($categoryIds)) {
                $condition = '';
            }
            else {
                $condition = array('in' => $categoryIds);
            }

        }
        elseif (is_numeric($categoryIds)) {
            $condition = $categoryIds;
        }
        elseif (is_string($categoryIds)) {
            $ids = explode(',', $categoryIds);
            if (empty($ids)) {
                $condition = $categoryIds;
            }
            else {
                $condition = array('in' => $ids);
            }
        }

        $this->addFieldToFilter('entity_id', $condition);
        return $this;
    }

    public function setLoadProductCount($flag)
    {
        $this->_loadWithProductCount = $flag;
        return $this;
    }

    public function setProductStoreId($storeId)
    {
        $this->_productStoreId = $storeId;
        return $this;
    }

    public function getProductStoreId()
    {
        if (is_null($this->_productStoreId)) {
            $this->_productStoreId = 0;
        }
        return $this->_productStoreId;
    }

    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->_loadWithProductCount) {
            $this->addAttributeToSelect('all_children');
            $this->addAttributeToSelect('is_anchor');
        }

        parent::load($printQuery, $logQuery);

        if ($this->_loadWithProductCount) {
            $this->_loadProductCount();
        }

        return $this;
    }

    /**
     * Load categories product count
     *
     * @return this
     */
    protected function _loadProductCount()
    {
        $anchor     = array();
        $regular    = array();

        foreach ($this->_items as $item) {
            if ($item->getIsAnchor()) {
                $anchor[$item->getId()] = $item;
            }
            else {
                $regular[$item->getId()] = $item;
            }
        }

        // Retrieve regular categories product counts
        $regularIds = array_keys($regular);
        if (!empty($regularIds)) {
            $select = $this->_conn->select();
            $select->from(
                    array('main_table'=>$this->_productTable),
                    array('category_id', new Zend_Db_Expr('COUNT(main_table.product_id)'))
                )
                ->where($this->_conn->quoteInto('main_table.category_id IN(?)', $regularIds))
                ->group('main_table.category_id');
            $counts = $this->_conn->fetchPairs($select);
            foreach ($regular as $item) {
                if (isset($counts[$item->getId()])) {
                    $item->setProductCount($counts[$item->getId()]);
                }
                else {
                    $item->setProductCount(0);
                }
            }
        }

        // Retrieve Anchor categories product counts
        foreach ($anchor as $item) {
            if ($allChildren = $item->getAllChildren()) {
                $select = $this->_conn->select();
                $select->from(
                        array('main_table'=>$this->_productTable),
                        new Zend_Db_Expr('COUNT( DISTINCT main_table.product_id)')
                    )
                    ->where($this->_conn->quoteInto('main_table.category_id IN(?)', explode(',', $item->getAllChildren())))
                    ->group('main_table.category_id');
                $item->setProductCount((int) $this->_conn->fetchOne($select));
            }
            else {
                $item->setProductCount(0);
            }
        }
        return $this;
    }

    public function addPathFilter($regexp)
    {
        $this->getSelect()->where(new Zend_Db_Expr("path regexp '{$regexp}'"));
        return $this;
    }
}
