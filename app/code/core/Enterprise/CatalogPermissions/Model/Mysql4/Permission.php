<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Enterprise
 * @package    Enterprise_CatalogPermissions
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Permission resource model
 *
 * @category   Enterprise
 * @package    Enterprise_CatalogPermissions
 */
class Enterprise_CatalogPermissions_Model_Mysql4_Permission extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Intialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('enterprise_catalogpermissions/permission', 'permission_id');
    }

    /**
     * Initialize unique scope for permission
     *
     * @return Enterprise_CatalogPermissions_Model_Mysql4_Permission
     */
    protected function _initUniqueFields()
    {
        parent::_initUniqueFields();
        $this->_uniqueFields[] = array(
            'field' => array('category_id', 'website_id', 'customer_group_id'),
            'title' => Mage::helper('enterprise_catalogpermissions')->__('Permission with same scope')
        );
    }
}