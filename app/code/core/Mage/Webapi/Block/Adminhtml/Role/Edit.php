<?php
/**
 * Web API role edit page
 *
 * @copyright {}
 *
 * @method Mage_Webapi_Block_Adminhtml_Role_Edit setApiRole() setApiRole(Mage_Webapi_Model_Acl_Role $role)
 * @method Mage_Webapi_Model_Acl_Role getApiRole() getApiRole()
 */
class Mage_Webapi_Block_Adminhtml_Role_Edit extends Mage_Backend_Block_Widget_Form_Container
{
    /**
     * Initialize form container
     */
    public function __construct()
    {
        $this->_blockGroup = 'Mage_Webapi';
        $this->_objectId = 'role_id';
        $this->_controller = 'adminhtml_role';

        parent::__construct();

        $this->_formScripts[] = "function saveAndContinueEdit(url)" .
            "{var tagForm = new varienForm('edit_form'); tagForm.submit(url);}";

        $this->_addButton('save_and_continue', array(
            'label' => Mage::helper('Mage_Webapi_Helper_Data')->__('Save and Continue Edit'),
            'onclick' => "saveAndContinueEdit('" . $this->getSaveAndContinueUrl() . "')",
            'class' => 'save'
        ), 100);

        $this->_updateButton('save', 'label', Mage::helper('Mage_Webapi_Helper_Data')->__('Save API Role'));
        $this->_updateButton('delete', 'label', Mage::helper('Mage_Webapi_Helper_Data')->__('Delete API Role'));
    }

    /**
     * Retrieve role SaveAndContinue URL
     *
     * @return string
     */
    public function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array('_current' => true, 'continue' => true));
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getApiRole()->getId()) {
            return Mage::helper('Mage_Webapi_Helper_Data')
                ->__("Edit API Role '%s'", $this->escapeHtml($this->getApiRole()->getRoleName()));
        } else {
            return Mage::helper('Mage_Webapi_Helper_Data')->__('New API Role');
        }
    }
}
