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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer addresses forms
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_Addresses extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('customer/tab/addresses.phtml');
    }

    public function getRegionsUrl()
    {
        return $this->getUrl('*/json/countryRegion');
    }

    protected function _prepareLayout()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'  => Mage::helper('customer')->__('Delete Address'),
                    'name'   => 'delete_address',
                    'element_name' => 'delete_address',
                    'disabled' => $this->isReadonly(),
                    'class'  => 'delete' . ($this->isReadonly() ? ' disabled' : '')
                ))
        );
        $this->setChild('add_address_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'  => Mage::helper('customer')->__('Add New Address'),
                    'id'     => 'add_address_button',
                    'name'   => 'add_address_button',
                    'element_name' => 'add_address_button',
                    'disabled' => $this->isReadonly(),
                    'class'  => 'add'  . ($this->isReadonly() ? ' disabled' : ''),
                    'onclick'=> 'customerAddresses.addNewAddress()'
                ))
        );
        $this->setChild('cancel_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'  => Mage::helper('customer')->__('Cancel'),
                    'id'     => 'cancel_add_address'.$this->getTemplatePrefix(),
                    'name'   => 'cancel_address',
                    'element_name' => 'cancel_address',
                    'class'  => 'cancel delete-address'  . ($this->isReadonly() ? ' disabled' : ''),
                    'disabled' => $this->isReadonly(),
                    'onclick'=> 'customerAddresses.cancelAdd(this)',
                ))
        );
        return parent::_prepareLayout();
    }

    /**
     * Check block is readonly.
     *
     * @return boolean
     */
    public function isReadonly()
    {
        $customer = Mage::registry('current_customer');
        return $customer->isReadonly();
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    /**
     * Initialize form object
     *
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Addresses
     */
    public function initForm()
    {
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::registry('current_customer');

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('address_fieldset', array(
            'legend'    => Mage::helper('customer')->__("Edit Customer's Address"))
        );

        $addressModel = Mage::getModel('customer/address');
        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('adminhtml_customer_address')
            ->setEntity($addressModel);

//        $this->_setFieldset($addressModel->getAttributes(), $fieldset);
        $this->_setFieldset($addressForm->getAttributes(), $fieldset);


        $regionElement = $form->getElement('region');
        if ($regionElement) {
            $regionElement->setRenderer(Mage::getModel('adminhtml/customer_renderer_region'));
        }

        $regionElement = $form->getElement('region_id');
        if ($regionElement) {
            $regionElement->setNoDisplay(true);
        }

        $country = $form->getElement('country_id');
        if ($country) {
            $country->addClass('countries');
        }

        if ($this->isReadonly()) {
            foreach ($addressModel->getAttributes() as $attribute) {
                $element = $form->getElement($attribute->getAttributeCode());
                if ($element) {
                    $element->setReadonly(true, true);
                }
            }
        }

        $addressCollection = $customer->getAddresses();
        $this->assign('customer', $customer);
        $this->assign('addressCollection', $addressCollection);
        $this->setForm($form);

        return $this;
    }

    public function getCancelButtonHtml()
    {
        return $this->getChildHtml('cancel_button');
    }

    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_address_button');
    }

    public function getTemplatePrefix()
    {
        return '_template_';
    }

    /**
     * Return predefined additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return array(
            'file'      => Mage::getConfig()->getBlockClassName('adminhtml/customer_form_element_file'),
            'image'     => Mage::getConfig()->getBlockClassName('adminhtml/customer_form_element_image'),
            'boolean'   => Mage::getConfig()->getBlockClassName('adminhtml/customer_form_element_boolean'),
        );
    }
}
