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
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Admin tax class edit page
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author          Alexander Stadnitski <alexander@varien.com>
 */

class Mage_Adminhtml_Block_Tax_Class_Page_Edit extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('tax/class/page/edit.phtml');
    }

    protected function _prepareLayout()
    {
        $classType = strtolower($this->getRequest()->getParam('classType'));
        $this->setChild('renameForm', $this->getLayout()->createBlock("adminhtml/tax_class_form_rename"));

        $this->setChild('backButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => __('Back'),
                    'onclick'   => 'window.location.href=\''.Mage::getUrl('*/tax_class_'. strtolower($this->getRequest()->getParam('classType')) ).'\'',
                    'class' => 'back'
                ))
        );

        $this->setChild('resetButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => __('Reset'),
                    'onclick'   => 'window.location.reload()'
                ))
        );

        $this->setChild('saveButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => __('Save Class'),
                    'onclick'   => 'renameForm.submit();return false;',
                    'class' => 'save'
                ))
        );

        $this->setChild('deleteButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => __('Delete Class'),
                    'onclick'   => 'deleteConfirm(\'' . __('Are you sure you want to do this?') . '\', \'' . Mage::getUrl('*/*/delete', array('classId' => $this->getRequest()->getParam('classId'), 'classType' => $this->getRequest()->getParam('classType'))) . '\')',
                    'class' => 'delete'
                ))
        );
        return parent::_prepareLayout();
    }

    protected function _getRenameFormHtml()
    {
        return $this->getChildHtml('renameForm');
    }

    protected function _getRenameFormId()
    {
        return $this->getChild('renameForm')->getForm()->getId();
    }

    protected function _getHeader()
    {
        return __('Edit Class Details');
    }

    public function getBackButtonHtml()
    {
        return $this->getChildHtml('backButton');
    }

    public function getResetButtonHtml()
    {
        return $this->getChildHtml('resetButton');
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('saveButton');
    }

    public function getDeleteButtonHtml()
    {
        if( intval($this->getRequest()->getParam('classId')) == 0 ) {
            return;
        }
        return $this->getChildHtml('deleteButton');
    }
}
