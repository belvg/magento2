<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_ProductAlert
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Product alert for changed price collection
 *
 * @category    Mage
 * @package     Mage_ProductAlert
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ProductAlert_Model_Resource_Price_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Define price collection
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_ProductAlert_Model_Price', 'Mage_ProductAlert_Model_Resource_Price');
    }

    /**
     * Add customer filter
     *
     * @param mixed $customer
     * @return Mage_ProductAlert_Model_Resource_Price_Collection
     */
    public function addCustomerFilter($customer)
    {
        if (is_array($customer)) {
            $condition = $this->getConnection()->quoteInto('customer_id IN(?)', $customer);
        } elseif ($customer instanceof Mage_Customer_Model_Customer) {
            $condition = $this->getConnection()->quoteInto('customer_id=?', $customer->getId());
        } else {
            $condition = $this->getConnection()->quoteInto('customer_id=?', $customer);
        }
        $this->addFilter('customer_id', $condition, 'string');
        return $this;
    }

    /**
     * Add website filter
     *
     * @param mixed $website
     * @return Mage_ProductAlert_Model_Resource_Price_Collection
     */
    public function addWebsiteFilter($website)
    {
        if (is_null($website) || $website == 0) {
            return $this;
        }
        if (is_array($website)) {
            $condition = $this->getConnection()->quoteInto('website_id IN(?)', $website);
        } elseif ($website instanceof Mage_Core_Model_Website) {
            $condition = $this->getConnection()->quoteInto('website_id=?', $website->getId());
        } else {
            $condition = $this->getConnection()->quoteInto('website_id=?', $website);
        }
        $this->addFilter('website_id', $condition, 'string');
        return $this;
    }

    /**
     * Set order by customer
     *
     * @param string $sort
     * @return Mage_ProductAlert_Model_Resource_Price_Collection
     */
    public function setCustomerOrder($sort = 'ASC')
    {
        $this->getSelect()->order('customer_id ' . $sort);
        return $this;
    }
}