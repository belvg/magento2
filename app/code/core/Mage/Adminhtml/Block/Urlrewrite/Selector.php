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
 * Modes selector for Urlrewrites modes
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Urlrewrite_Selector extends Mage_Core_Block_Template
{
    /**
     * List of available modes from source model
     * key => label
     *
     * @var array
     */
    protected $_modes;

    protected $_template = 'urlrewrite/selector.phtml';

    /**
     * Set block template and get available modes
     *
     */
    protected function _construct()
    {

        $this->_modes = array(
            'category' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('For category'),
            'product'  => Mage::helper('Mage_Adminhtml_Helper_Data')->__('For product'),
            'id'       => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Custom'),
        );
    }

    /**
     * Available modes getter
     *
     * @return array
     */
    public function getModes()
    {
        return $this->_modes;
    }

    /**
     * Label getter
     *
     * @return array
     */
    public function getSelectorLabel()
    {
        return Mage::helper('Mage_Adminhtml_Helper_Data')->__('Create URL Rewrite:');
    }

    /**
     * Check whether selection is in specified mode
     *
     * @param string $mode
     * @return bool
     */
    public function isMode($mode)
    {
        return $this->getRequest()->has($mode);
    }
}
