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
 * Adminhtml permissions row block
 *
 * @category   Enterprise
 * @package    Enterprise_CatalogPermissions
 */
class Enterprise_CatalogPermissions_Block_Adminhtml_Catalog_Category_Tab_Permissions_Row
    extends Mage_Adminhtml_Block_Catalog_Category_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('enterprise/catalogpermissions/catalog/category/tab/permissions/row.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('delete_button', $this->getLayout()->createBlock('adminhtml/widget_button')
            ->addData(array(
                'label' => $this->helper('enterprise_catalogpermissions')->__('Remove Permission'),
                'class' => 'delete',
                'type'  => 'button',
                'id'    => '{{html_id}}_delete_button'
            ))
        );

        return parent::_prepareLayout();
    }

    /**
     * Check edit by websites
     *
     * @return boolean
     */
    public function canEditWebsites()
    {
        return !Mage::app()->isSingleStoreMode();
    }

    public function getDefaultWebsiteId()
    {
        return Mage::app()->getStore(true)->getWebsiteId();
    }

    /**
     * Retrieve list of permission grants
     *
     * @return array
     */
    public function getGrants()
    {
        return array(
            'grant_catalog_category_view' => $this->helper('enterprise_catalogpermissions')->__('Category Access'),
            'grant_catalog_product_price' => $this->helper('enterprise_catalogpermissions')->__('View Product Prices'),
            'grant_checkout_items' => $this->helper('enterprise_catalogpermissions')->__('Add Products to Cart')
        );
    }

    /**
     * Retrieve field class name
     *
     * @param string $fieldId
     * @return string
     */
    public function getFieldClassName($fieldId)
    {
        return strtr($fieldId, '_', '-') . '-value';
    }

    /**
     * Retrieve websites collection
     *
     * @return Mage_Core_Model_Mysql4_Website_Collection
     */
    public function getWebsiteCollection()
    {
        if (!$this->hasData('website_collection')) {
            $collection = Mage::getModel('core/website')->getCollection();
            $this->setData('website_collection', $collection);
        }

        return $this->getData('website_collection');
    }

    /**
     * Retrieve customer group collection
     *
     * @return Mage_Customer_Model_Entity_Group_Collection
     */
    public function getCustomerGroupCollection()
    {
        if (!$this->hasData('customer_group_collection')) {
            $collection = Mage::getModel('customer/group')->getCollection();
            $this->setData('customer_group_collection', $collection);
        }

        return $this->getData('customer_group_collection');
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }
}