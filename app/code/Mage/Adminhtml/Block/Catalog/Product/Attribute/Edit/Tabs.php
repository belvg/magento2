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
 * Adminhtml product attribute edit page tabs
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('product_attribute_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('Mage_Catalog_Helper_Data')->__('Attribute Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            'main',
            array(
                'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Properties'),
                'title'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Properties'),
                'content'   => $this->getChildHtml('main'),
                'active'    => true
            )
        );
        $this->addTab(
            'labels',
            array(
                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Manage Label'),
                'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Manage Label'),
                'content' => $this->getChildHtml('labels'),
            )
        );
        $this->addTab(
            'front',
            array(
                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Frontend Properties'),
                'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Frontend Properties'),
                'content' => $this->getChildHtml('front'),
            )
        );

        return parent::_beforeToHtml();
    }

}
