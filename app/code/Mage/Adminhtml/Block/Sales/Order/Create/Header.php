<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Create order form header
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Create_Header extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{
    protected function _toHtml()
    {
        if ($this->_getSession()->getOrder()->getId()) {
            return '<h3 class="icon-head head-sales-order">'.Mage::helper('Mage_Sales_Helper_Data')->__('Edit Order #%s', $this->_getSession()->getOrder()->getIncrementId()).'</h3>';
        }

        $customerId = $this->getCustomerId();
        $storeId    = $this->getStoreId();
        $out = '';
        if ($customerId && $storeId) {
            $out.= Mage::helper('Mage_Sales_Helper_Data')->__('Create New Order for %s in %s', $this->getCustomer()->getName(), $this->getStore()->getName());
        }
        elseif (!is_null($customerId) && $storeId){
            $out.= Mage::helper('Mage_Sales_Helper_Data')->__('Create New Order for New Customer in %s', $this->getStore()->getName());
        }
        elseif ($customerId) {
            $out.= Mage::helper('Mage_Sales_Helper_Data')->__('Create New Order for %s', $this->getCustomer()->getName());
        }
        elseif (!is_null($customerId)){
            $out.= Mage::helper('Mage_Sales_Helper_Data')->__('Create New Order for New Customer');
        }
        else {
            $out.= Mage::helper('Mage_Sales_Helper_Data')->__('Create New Order');
        }
        $out = $this->escapeHtml($out);
        $out = '<h3 class="icon-head head-sales-order">' . $out . '</h3>';
        return $out;
    }
}