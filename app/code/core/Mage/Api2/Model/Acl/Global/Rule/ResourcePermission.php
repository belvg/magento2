<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Api2
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * API2 Global ACL role resources permissions model
 *
 * @category    Mage
 * @package     Mage_Api2
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Acl_Global_Rule_ResourcePermission
    implements Mage_Api2_Model_Acl_PermissionInterface
{
    /**
     * Resources permissions
     *
     * @var array
     */
    protected $_resourcesPermissions;

    /**
     * Role
     *
     * @var Mage_Api2_Model_Acl_Global_Role
     */
    protected $_role;

    /**
     * Get resources permissions for selected role
     *
     * @return array
     */
    public function getResourcesPermissions()
    {
        if (null === $this->_resourcesPermissions) {
            $roleConfigNodeName = $this->_role->getConfigNodeName();
            $rulesPairs = array();
            $allowedType = Mage_Api2_Model_Acl_Global_Rule_Permission::TYPE_ALLOW;

            if ($this->_role) {
                /** @var $rules Mage_Api2_Model_Resource_Acl_Global_Rule_Collection */
                $rules = Mage::getResourceModel('Mage_Api2_Model_Resource_Acl_Global_Rule_Collection');
                $rules->addFilterByRoleId($this->_role->getId());

                /** @var $rule Mage_Api2_Model_Acl_Global_Rule */
                foreach ($rules as $rule) {
                    $resourceId = $rule->getResourceId();
                    $rulesPairs[$resourceId]['privileges'][$roleConfigNodeName][$rule->getPrivilege()] = $allowedType;
                }
            } else {
                //make resource "all" as default for new item
                $rulesPairs = array(Mage_Api2_Model_Acl_Global_Rule::RESOURCE_ALL => $allowedType);
            }

            //set permissions to resources
            /** @var $config Mage_Api2_Model_Config */
            $config = Mage::getModel('Mage_Api2_Model_Config');
            /** @var $privilegeSource Mage_Api2_Model_Acl_Global_Rule_Privilege */
            $privilegeSource = Mage::getModel('Mage_Api2_Model_Acl_Global_Rule_Privilege');
            $privileges = array_keys($privilegeSource->toArray());

            /** @var $node Varien_Simplexml_Element */
            foreach ($config->getResources() as $resourceType => $node) {
                $resourceId = (string)$resourceType;
                $allowedRoles = (array)$node->privileges;
                $allowedPrivileges = array();
                if (isset($allowedRoles[$roleConfigNodeName])) {
                    $allowedPrivileges = $allowedRoles[$roleConfigNodeName];
                }
                foreach ($privileges as $privilege) {
                    if (empty($allowedPrivileges[$privilege])
                        && isset($rulesPairs[$resourceId][$roleConfigNodeName]['privileges'][$privilege])
                    ) {
                        unset($rulesPairs[$resourceId][$roleConfigNodeName]['privileges'][$privilege]);
                    } elseif (!empty($allowedPrivileges[$privilege])
                        && !isset($rulesPairs[$resourceId][$roleConfigNodeName]['privileges'][$privilege])
                    ) {
                        $deniedType = Mage_Api2_Model_Acl_Global_Rule_Permission::TYPE_DENY;
                        $rulesPairs[$resourceId]['privileges'][$roleConfigNodeName][$privilege] = $deniedType;
                    }
                }
            }
            $this->_resourcesPermissions = $rulesPairs;
        }
        return $this->_resourcesPermissions;
    }

    /**
     * Set filter value
     *
     * @param Mage_Api2_Model_Acl_Global_Role $role
     */
    public function setFilterValue($role)
    {
        if ($role && $role->getId()) {
            $this->_role = $role;
        }
    }
}
