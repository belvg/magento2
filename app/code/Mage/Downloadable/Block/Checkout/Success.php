<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Downloadable
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Downloadable checkout success page
 *
 * @category   Mage
 * @package    Mage_Downloadable
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Downloadable_Block_Checkout_Success extends Mage_Checkout_Block_Onepage_Success
{

    /**
     * Return true if order(s) has one or more downloadable products
     *
     * @return bool
     */
    public function getOrderHasDownloadable()
    {
        $hasDownloadableFlag = Mage::getSingleton('Mage_Checkout_Model_Session')
            ->getHasDownloadableProducts(true);
        if (!$this->isOrderVisible()) {
            return false;
        }
        /**
         * if use guest checkout
         */
        if (!Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId()) {
            return false;
        }
        return $hasDownloadableFlag;
    }

    /**
     * Return url to list of ordered downloadable products of customer
     *
     * @return string
     */
    public function getDownloadableProductsUrl()
    {
        return $this->getUrl('downloadable/customer/products', array('_secure' => true));
    }
}