<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Enterprise_Rma_Block_Order_Guest extends Mage_Core_Block_Template
{
    public function _construct()
    {
        parent::_construct();

        if (Mage::helper('Enterprise_Rma_Helper_Data')->isEnabled()) {
            $returns = Mage::getResourceModel('Enterprise_Rma_Model_Resource_Rma_Grid_Collection')
                ->addFieldToSelect('*')
                ->addFieldToFilter('order_id', Mage::registry('current_order')->getId())
                ->count()
            ;

            if (!empty($returns)) {
                Mage::app()->getLayout()
                    ->getBlock('sales.order.info')
                    ->addLink('returns', 'rma/guest/returns', 'Returns');
            }
        }
    }
}