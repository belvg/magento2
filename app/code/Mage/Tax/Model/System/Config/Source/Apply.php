<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Tax
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Tax_Model_System_Config_Source_Apply
{
    protected $_options;

    public function __construct()
    {
        $this->_options = array(
            array(
                'value' => 0,
                'label' => Mage::helper('Mage_Tax_Helper_Data')->__('Before Discount')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('Mage_Tax_Helper_Data')->__('After Discount')
            ),
        );
    }

    public function toOptionArray()
    {
        return $this->_options;
    }
}