<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @package    Enterprise_Cms
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Manage revision controller
 *
 * @category    Enterprise
 * @package     Enterprise_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Enterprise_Cms_Adminhtml_Cms_Page_RevisionController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init actions
     *
     * @return Enterprise_Cms_Adminhtml_Cms_Page_RevisionController
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('cms/page')
            ->_addBreadcrumb(Mage::helper('cms')->__('CMS'), Mage::helper('cms')->__('CMS'))
            ->_addBreadcrumb(Mage::helper('cms')->__('Manage Pages'), Mage::helper('cms')->__('Manage Pages'))
        ;
        return $this;
    }

    /**
     * Prepare and place revision model into registry
     * with loaded data if id parameter present
     *
     * @param string $idFieldName
     * @return Enterprise_Cms_Model_Page_Revision
     */
    protected function _initRevision()
    {
        $revisionId = (int) $this->getRequest()->getParam('revision_id');

        $revision = Mage::getModel('enterprise_cms/page_revision');

        if ($revisionId) {
            $revision->setUserId(Mage::getSingleton('admin/session')->getUser()->getId());
            $revision->setAccessLevel(Mage::getSingleton('enterprise_cms/config')->getAllowedAccessLevel());
            $revision->load($revisionId);
        }

        //setting in registry as cms_page to make work CE blocks
        Mage::register('cms_page', $revision);
        return $revision;
    }

    /**
     * Edit revision of CMS page
     */
    public function editAction()
    {
        $revision = $this->_initRevision();

        if (!$revision->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('enterprise_cms')->__('Could not load specified revision.'));

            $this->_redirect('*/cms_page/edit',
                array('page_id' => $this->getRequest()->getParam('page_id')));
            return;
        }

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $_data = $revision->getData();
            $_data = array_merge($_data, $data);
            $revision->setData($_data);
        }

        $this->_initAction()
            ->_addBreadcrumb($revision->getId() ? Mage::helper('enterprise_cms')->__('Edit Revision')
                    : Mage::helper('enterprise_cms')->__('New Revision'),
                $revision->getId() ? Mage::helper('enterprise_cms')->__('Edit Revision')
                    : Mage::helper('enterprise_cms')->__('New Revision'));

        $this->renderLayout();
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        // check if data sent
        if ($data = $this->getRequest()->getPost()) {
            // init model and set data
            $revision = $this->_initRevision();
            $revision->setData($data);

            // try to save it
            try {
                // save the data
                $revision->save();

                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('enterprise_cms')->__('Revision was successfully saved.'));
                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/' . $this->getRequest()->getParam('back'),
                        array(
                            'page_id' => $revision->getPageId(),
                            'revision_id' => $revision->getId()
                        ));
                    return;
                }
                // go to grid
                $this->_redirect('*/cms_page/edit', array('page_id' => $revision->getPageId()));
                return;

            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // save data in session
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                // redirect to edit form
                $this->_redirect('*/*/edit',
                    array(
                        'page_id' => $this->getRequest()->getParam('page_id'),
                        'revision_id' => $this->getRequest()->getParam('revision_id'),
                        ));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Action for version info ajax tab
     *
     * @return Enterprise_Cms_Adminhtml_Cms_Page_RevisionController
     */
    public function versionAction()
    {
        $this->_initRevision();

        $this->loadLayout();
        $this->renderLayout();

        return $this;
    }

    /**
     * Publishing revision
     */
    public function publishAction()
    {
        $revision = $this->_initRevision();

        try {
            $revision->publish();
            // display success message
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('enterprise_cms')->__('Revision was successfully published.'));
            $this->_redirect('*/cms_page/edit', array('page_id' => $revision->getPageId()));
            return;
        } catch (Exception $e) {
            // display error message
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            // redirect to edit form
            $this->_redirect('*/*/edit', array(
                    'page_id' => $this->getRequest()->getParam('page_id'),
                    'revision_id' => $this->getRequest()->getParam('revision_id')
                    ));
            return;
        }
    }

    /**
     * Preview action
     *
     * @return Enterprise_Cms_Adminhtml_Cms_Page_RevisionController
     */
    public function previewAction()
    {
        // check if data sent
        if ($data = $this->getRequest()->getPost()) {
            // init model and set data
            $page = Mage::getSingleton('cms/page')
                ->load($data['page_id']);
            if (!$page->getId()) {
                $this->_forward('noRoute');
            }

            /*
             * Preparing posted data for settign it in page model
             */
            $attributes = Mage::getSingleton('enterprise_cms/config')
                ->getPageRevisionControledAttributes();

            foreach ($data as $key => $value) {
                if (in_array($key, $attributes)) {
                    $page->setData($key, $value);
                }
            }

            /*
             * Retrieve store id from page model or if it was passed from post
             */
            $selectedStoreId = $page->getStoreId();
            if (is_array($selectedStoreId)) {
                $selectedStoreId = array_shift($selectedStoreId);
                if (!$selectedStoreId) {
                    $allStores = true;
                } else {
                    $allStores = false;
                }
            } else {
                $allStores = true;
            }

            if (isset($data['store_switcher'])) {
                $selectedStoreId = $data['store_switcher'];
            } else {
                if (!$selectedStoreId) {
                    $selectedStoreId = Mage::app()->getDefaultStoreView()->getId();
                }
            }
            $selectedStoreId = (int) $selectedStoreId;

            /*
             * Emulating front environment
             */
            Mage::app()->setCurrentStore(Mage::app()->getStore($selectedStoreId));

            Mage::getDesign()->setArea('frontend')
                ->setStore($selectedStoreId);

            $designChange = Mage::getSingleton('core/design')
                ->loadChange($selectedStoreId);

            if ($designChange->getData()) {
                Mage::getDesign()->setPackageName($designChange->getPackage())
                    ->setTheme($designChange->getTheme());
            }

            Mage::helper('cms/page')->renderPageExtended($this, null, false);


            /*
             * Adding store switcher block
             */
            $block = $this->getLayout()
                ->addBlock('enterprise_cms/store_switcher', 'store_switcher');

            if (!$allStores) {
                $block->setStoreIds($page->getStoreId());
            }

            $block->setStoreId($selectedStoreId);
            $block->setRepostData($data);

            $this->getLayout()
                ->getBlock('before_body_end')
                ->append('store_switcher');

            $this->renderLayout();
        } else {
            $this->_forward('noRoute');
        }

        return $this;
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        // check if we know what should be deleted
        if ($id = $this->getRequest()->getParam('revision_id')) {
            try {
                // init model and delete
                $revision = $this->_initRevision();
                $revisionNumber = $revision->getRevisionNumber();
                $revision->delete();
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('enterprise_cms')->__('Revision was successfully deleted.'));
                $this->_redirect('*/cms_page/edit', array('page_id' => $revision->getPageId()));
                return;
            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // go back to edit form
                $this->_redirect('*/*/edit', array('_current' => true));
                return;
            }
        }
        // display error message
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('enterprise_cms')->__('Unable to find a revision to delete.'));
        // go to grid
        $this->_redirect('*/cms_page/edit', array('_current' => true));
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'save':
                return Mage::getSingleton('enterprise_cms/config')->isCurrentUserCanSaveRevision();
                break;
            case 'publish':
                return Mage::getSingleton('enterprise_cms/config')->isCurrentUserCanPublishRevision();
                break;
            case 'delete':
                return Mage::getSingleton('enterprise_cms/config')->isCurrentUserCanDeleteRevision();
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('cms/page');
                break;
        }
    }

    /**
     * Controller predispatch method
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function preDispatch()
    {
        if ($this->getRequest()->getActionName() == 'preview') {
            $this->_currentArea = 'frontend';
        }
        parent::preDispatch();
    }
}
