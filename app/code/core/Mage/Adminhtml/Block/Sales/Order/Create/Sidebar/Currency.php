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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
/**
 * Sidebar currency switcher
 *
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Create_Sidebar_Currency extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{
    public function __construct() 
    {
        parent::__construct();
        $this->setTemplate('sales/order/create/sidebar/currency.phtml');
    }
    
    /**
     * Retrieve avilable currency codes
     *
     * @return unknown
     */
    public function getAvailableCurrencies()
    {
        $codes = $this->getStore()->getAvailableCurrencyCodes();
        return $codes;
    }
    
    /**
     * Retrieve curency name by code
     *
     * @param   string $code
     * @return  string
     */
    public function getCurrencyName($code)
    {
        return Mage::app()->getLocale()->getLocale()->getTranslation($code, 'currency');
    }
    
    /**
     * Retrieve current order currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->getStore()->getCurrentCurrencyCode();
    }
    
    public function hasItems()
    {
        return count($this->getAvailableCurrencies());
    }
}
