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
 * Adminhtml store delete group block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_System_Store_Delete_Group extends Mage_Adminhtml_Block_Template
{
    protected function _prepareLayout()
    {
        $itemId = $this->getRequest()->getParam('group_id');

        $this->setTemplate('system/store/delete_group.phtml');
        $this->setAction($this->getUrl('*/*/deleteGroupPost', array('group_id'=>$itemId)));
        $this->addChild('confirm_deletion_button', 'Mage_Adminhtml_Block_Widget_Button', array(
            'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Delete Store'),
            'onclick'   => "deleteForm.submit()",
            'class'     => 'cancel'
        ));
        $onClick = "setLocation('".$this->getUrl('*/*/editGroup', array('group_id'=>$itemId))."')";
        $this->addChild('cancel_button', 'Mage_Adminhtml_Block_Widget_Button', array(
            'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Cancel'),
            'onclick'   => $onClick,
            'class'     => 'cancel'
        ));
        $this->addChild('back_button', 'Mage_Adminhtml_Block_Widget_Button', array(
            'label'     => Mage::helper('Mage_Core_Helper_Data')->__('Back'),
            'onclick'   => $onClick,
            'class'     => 'cancel'
        ));
        return parent::_prepareLayout();
    }
}
