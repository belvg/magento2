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
 * Adminhtml backend model for "Use Custom Admin URL" option
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Model_System_Config_Backend_Admin_Usecustom extends Mage_Core_Model_Config_Data
{
    /**
     * Validate custom url
     *
     * @return Mage_Adminhtml_Model_System_Config_Backend_Admin_Usecustom
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        if ($value == 1) {
            $customUrl = $this->getData('groups/url/fields/custom/value');
            if (empty($customUrl)) {
                Mage::throwException(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Please specify the admin custom URL.'));
            }
        }

        return $this;
    }

    /**
     * Delete custom admin url from configuration if "Use Custom Admin Url" option disabled
     *
     * @return Mage_Adminhtml_Model_System_Config_Backend_Admin_Usecustom
     */
    protected function _afterSave()
    {
        $value = $this->getValue();

        if (!$value) {
            Mage::getConfig()->deleteConfig(
                Mage_Adminhtml_Model_System_Config_Backend_Admin_Custom::XML_PATH_SECURE_BASE_URL,
                Mage_Adminhtml_Model_System_Config_Backend_Admin_Custom::CONFIG_SCOPE,
                Mage_Adminhtml_Model_System_Config_Backend_Admin_Custom::CONFIG_SCOPE_ID
            );
            Mage::getConfig()->deleteConfig(
                Mage_Adminhtml_Model_System_Config_Backend_Admin_Custom::XML_PATH_UNSECURE_BASE_URL,
                Mage_Adminhtml_Model_System_Config_Backend_Admin_Custom::CONFIG_SCOPE,
                Mage_Adminhtml_Model_System_Config_Backend_Admin_Custom::CONFIG_SCOPE_ID
            );
        }

        return $this;
    }
}
