<?php
/**
 * Adminhtml tax rate controller
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Alexander Stadnitski <alexander@varien.com>
 */

class Mage_Adminhtml_Tax_RateController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout('baseframe');
        $this->_setActiveMenu('sales');
        $this->_addBreadcrumb(__('Tax rules'), __('Tax rules title'), Mage::getUrl('adminhtml/tax'));
        $this->_addBreadcrumb(__('Tax rates'), __('Tax rates title'));

        $this->_addTabs();

        $this->_addContent(
            $this->getLayout()->createBlock('adminhtml/tax_rate_toolbar_add', 'tax_rate_toolbar')
            ->assign('createUrl', Mage::getUrl('adminhtml/tax_rate/add'))
            ->assign('header', __('Tax rates'))
        );
        $this->_addContent($this->getLayout()->createBlock('adminhtml/tax_rate_grid', 'tax_rate_grid'));

        $this->renderLayout();
    }

    public function addAction()
    {
        $this->loadLayout('baseframe');
        $this->_setActiveMenu('sales');
        $this->_addBreadcrumb(__('Tax rules'), __('Tax rules title'), Mage::getUrl('adminhtml/tax'));
        $this->_addBreadcrumb(__('Tax rates'), __('Tax rates title'), Mage::getUrl('adminhtml/tax_rate'));
        $this->_addBreadcrumb(__('New tax rate'), __('New tax rate title'));

        $this->_addTabs();

        $form = $this->getLayout()->createBlock('adminhtml/tax_rate_form_add');

        $this->_addContent(
            $this->getLayout()->createBlock('adminhtml/tax_rate_toolbar_save')
            ->assign('header', __('Add new tax rate'))
            ->assign('form', $form)
        );

        $this->renderLayout();
    }

    public function saveAction()
    {
        if( $postData = $this->getRequest()->getPost() ) {
            try {
                $rateModel = Mage::getSingleton('tax/rate');
                $rateModel->setData($postData);
                $rateModel->save();
                $this->getResponse()->setRedirect(Mage::getUrl("*/*/"));
            } catch (Exception $e) {
                if ($referer = $this->getRequest()->getServer('HTTP_REFERER')) {
                    $this->getResponse()->setRedirect($referer);
                }
                # FIXME !!!!
            }
        }
    }

    public function editAction()
    {
        $this->loadLayout('baseframe');
        $this->_setActiveMenu('sales');
        $this->_addBreadcrumb(__('Tax rules'), __('Tax rules title'), Mage::getUrl('adminhtml/tax'));
        $this->_addBreadcrumb(__('Tax rates'), __('Tax rates title'), Mage::getUrl('adminhtml/tax_rate'));
        $this->_addBreadcrumb(__('Edit tax rate'), __('Edit tax rate title'));

        $this->_addTabs();

        $form = $this->getLayout()->createBlock('adminhtml/tax_rate_form_add');

        $this->_addContent(
            $this->getLayout()->createBlock('adminhtml/tax_rate_toolbar_save')
            ->assign('header', __('Edit tax rate'))
            ->assign('form', $form)
        );

        $this->renderLayout();
    }

    public function deleteAction()
    {
        if( $rateId = $this->getRequest()->getParam('rate') ) {
            try {
                $rateModel = Mage::getSingleton('tax/rate');
                $rateModel->setRateId($rateId);
                $rateModel->delete();
                $this->getResponse()->setRedirect(Mage::getUrl("*/*/"));
            } catch (Exception $e) {
                if ($referer = $this->getRequest()->getServer('HTTP_REFERER')) {
                    $this->getResponse()->setRedirect($referer);
                }
                # FIXME !!!!
            }
        }
    }

    protected function _addTabs($tabId='tax_rate')
    {
        $tabs = $this->getLayout()->createBlock('adminhtml/tax_tabs')
            ->setActiveTab($tabId);
        $this->_addLeft($tabs);
    }
}