<?php
/**
 * {license}
 *
 * @category    Mage
 * @package     Mage_Api2
 */

/**
 * API2 User Guest Class
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Auth_User_Guest extends Mage_Api2_Model_Auth_User_Abstract
{
    /**
     * User type
     */
    const USER_TYPE = 'guest';

    /**
     * Retrieve user human-readable label
     *
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('Mage_Api2_Helper_Data')->__('Guest');
    }

    /**
     * Retrieve user type
     *
     * @return string
     */
    public function getType()
    {
        return self::USER_TYPE;
    }

    /**
     * Retrieve user role
     *
     * @return int
     */
    public function getRole()
    {
        if (!$this->_role) {
            /** @var $role Mage_Api2_Model_Acl_Global_Role */
            $role = Mage::getModel('Mage_Api2_Model_Acl_Global_Role')->load(Mage_Api2_Model_Acl_Global_Role::ROLE_GUEST_ID);
            if (!$role->getId()) {
                throw new Exception('Guest role not found');
            }

            $this->_role = Mage_Api2_Model_Acl_Global_Role::ROLE_GUEST_ID;
        }

        return $this->_role;
    }
}
