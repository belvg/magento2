<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Adminhtml_Model_System_Config_Source_Price_Step
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Mage_Catalog_Model_Layer_Filter_Price::RANGE_CALCULATION_AUTO,
                'label' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Automatic')
            ),
            array(
                'value' => Mage_Catalog_Model_Layer_Filter_Price::RANGE_CALCULATION_IMPROVED,
                'label' => Mage::helper('adminhtml')->__('Continuous')
            ),
            array(
                'value' => Mage_Catalog_Model_Layer_Filter_Price::RANGE_CALCULATION_MANUAL,
                'label' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Manual')
            ),
        );
    }
}
