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
 * @package    Mage_Chronopay
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Chronopay Configuration Model
 *
 * @category   Mage
 * @package    Mage_Chronopay
 * @name       Mage_Chronopay_Model_Config
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Chronopay_Model_Config extends Varien_Object
{
    /**
     *  Return config var
     *
     *  @param    string Var key
     *  @param    string Default value for non-existing key
     *  @return	  mixed
     */
    public function getConfigData($key, $default=false)
    {
        if (!$this->hasData($key)) {
             $value = Mage::getStoreConfig('payment/chronopay_standard/'.$key);
             $value = is_null($value) || false===$value
                      ? Mage::getStoreConfig('chronopay/standard/'.$key)
                      : $value;
             if (is_null($value) || false===$value) {
                 $value = $default;
             }
            $this->setData($key, $value);
        }
        return $this->getData($key);
    }

    /**
     *  Return Site ID registered in ChronoPay Admnin Panel
     *
     *  @param    none
     *  @return	  string Site ID
     */
    public function getSiteId ()
    {
        return $this->getConfigData('site_id');
    }

    /**
     *  Return Product ID (general type payments) registered in ChronoPay Admnin Panel
     *
     *  @param    none
     *  @return	  string Product ID
     */
    public function getProductId ()
    {
        return $this->getConfigData('product_id');
    }

    /**
     *  Return Store description sent to Chronopay
     *
     *  @return	  string Description
     */
    public function getDescription ()
    {
        $description = $this->getConfigData('description');
        if (!$description) {
            $description = Mage::app()->getStore()->getName() . ' ' . ' payment';
        }
        return $description;
    }

    /**
     *  Return new order status
     *
     *  @return	  string New order status
     */
    public function getNewOrderStatus ()
    {
        return $this->getConfigData('order_status');
    }

    /**
     *  Return debug flag
     *
     *  @return	  boolean Debug flag (0/1)
     */
    public function getDebug ()
    {
        return $this->getConfigData('debug_flag');
    }

    /**
     *  Return accepted currency
     *
     *  @param    none
     *  @return	  string Currenc
     */
    public function getCurrency ()
    {
        return $this->getConfigData('currency');
    }

    /**
     *  Return client interface language
     *
     *  @param    none
     *  @return	  string(2) Accepted language
     */
    public function getLanguage ()
    {
        return $this->getConfigData('language');
    }
}