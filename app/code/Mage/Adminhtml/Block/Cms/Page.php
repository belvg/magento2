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
 * Adminhtml cms pages content block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Cms_Page extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    /**
     * Block constructor
     */
    protected function _construct()
    {
        $this->_controller = 'cms_page';
        $this->_headerText = Mage::helper('Mage_Cms_Helper_Data')->__('Manage Pages');

        parent::_construct();

        if ($this->_isAllowedAction('Mage_Cms::save')) {
            $this->_updateButton('add', 'label', Mage::helper('Mage_Cms_Helper_Data')->__('Add New Page'));
        } else {
            $this->_removeButton('add');
        }

    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed($resourceId);
    }

}