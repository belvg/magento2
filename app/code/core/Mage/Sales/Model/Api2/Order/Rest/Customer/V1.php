<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * API2 class for orders (customer)
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Api2_Order_Rest_Customer_V1 extends Mage_Sales_Model_Api2_Order_Rest
{
    /**
     * Retrieve collection instance for orders
     *
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    protected function _getCollectionForRetrieve()
    {
        return parent::_getCollectionForRetrieve()->addAttributeToFilter(
            'customer_id', array('eq' => $this->getApiUser()->getUserId())
        );
    }

    /**
     * Retrieve collection instance for single order
     *
     * @param int $orderId Order identifier
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    protected function _getCollectionForSingleRetrieve($orderId)
    {
        return parent::_getCollectionForSingleRetrieve($orderId)->addAttributeToFilter(
            'customer_id', array('eq' => $this->getApiUser()->getUserId())
        );
    }

    /**
     * Prepare and return order comments collection
     *
     * @param array $orderIds Orders' identifiers
     * @return Mage_Sales_Model_Resource_Order_Status_History_Collection|Object
     */
    protected function _getCommentsCollection(array $orderIds)
    {
        return parent::_getCommentsCollection($orderIds)->addFieldToFilter('is_visible_on_front', 1);
    }
}
