<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category    Enterprise
 * @package     Enterprise_Checkout
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Accordion grid for recently ordered products
 *
 * @category   Enterprise
 * @package    Enterprise_Checkout
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_Block_Adminhtml_Manage_Accordion_Ordered 
    extends Enterprise_Checkout_Block_Adminhtml_Manage_Accordion_Abstract
{
    /**
     * Initialize Grid
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('ordered_grid');
        $this->setHeaderText(
            Mage::helper('enterprise_checkout')->__('Last ordered items (%s)', $this->getItemsCount())
        );
    }

    /**
     * Prepare customer wishlist product collection
     *
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Wishlist
     */
    public function getItemsCollection()
    {
        if (!$this->hasData('items_collection')) {
            $storeIds = $this->_getStore()->getWebsite()->getStoreIds();
            /* @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
            $collection = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToFilter('customer_id', $this->_getCustomer()->getId())
                ->addAttributeToFilter('store_id', array('in' => $storeIds))
                ->addAttributeToSort('created_at', 'desc')
                ->setPage(1, 1)
                ->load();
            foreach ($collection as $order) {
                break;
            }
            if (isset($order)) {
                $collection = $order->getItemsCollection(); 
                foreach ($collection as $item) {
                    if ($item->getParentItem()) {
                        $collection->removeItemByKey($item->getId());
                    }
                }
            }
            if (isset($order)) {
                $collection = Mage::helper('enterprise_checkout')->applyProductTypesFilter($collection);
            }
            $this->setData('items_collection', isset($order) ? $collection : false);
        }
        return $this->getData('items_collection');
    }

    /**
     * Prepare Grid columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('product_name', array(
            'header'    => Mage::helper('customer')->__('Product name'),
            'index'     => 'name',
            'sortable'  => false
        ));

        $this->addColumn('price', array(
            'header'    => Mage::helper('sales')->__('Price'),
            'align'     => 'right',
            'type'      => 'price',
            'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
            'index'     => 'price',
            'sortable'  => false
        ));

        $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'field_name'=> 'add_order_item',
            'align'     => 'center',
            'index'     => 'item_id',
        ));
        
        return $this;
    }

    /**
     * Return grid URL for sorting and filtering
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/viewOrdered', array('_current'=>true));
    }
}
