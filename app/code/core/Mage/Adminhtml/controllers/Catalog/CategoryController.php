<?php
/**
 * Catalog category controller
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Adminhtml_Catalog_CategoryController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Catalog categories index action
     */
    public function indexAction()
    {
        $this->_forward('edit');
    }
    
    /**
     * Add new category form
     */
    public function addAction()
    {
        $this->_forward('edit');
    }
    
    /**
     * Edit category page
     */
    public function editAction()
    {
        $this->loadLayout('baseframe');
        $this->_setActiveMenu('catalog/categories');
        $this->getLayout()->getBlock('root')->setCanLoadExtJs(true);
        
        Mage::register('category', Mage::getModel('catalog/category'));
        if ($id = (int) $this->getRequest()->getParam('id')) {
            Mage::registry('category')->load($id);
        }
        
        //$this->_addBreadcrumb(__('Catalog'), __('Catalog Title'));
        $this->_addBreadcrumb(__('Manage Catalog Categories'), __('Manage Categories Title'));
        
        $this->_addLeft(
            $this->getLayout()->createBlock('adminhtml/catalog_category_tree')
        );
        
        $this->_addContent(
            $this->getLayout()->createBlock('adminhtml/catalog_category_edit')
        );
        
        $this->renderLayout();
    }
    
    /**
     * Move category tree node action
     */
    public function moveAction()
    {
        $nodeId         = $this->getRequest()->getPost('id', false);
        $parentNodeId   = $this->getRequest()->getPost('pid', false);
        $prevNodeId     = $this->getRequest()->getPost('aid', false);
        
        try {
            $tree = Mage::getResourceModel('catalog/category_tree')->getTree();
            $node = $tree->loadNode($nodeId);
            $parentNode = $tree->loadNode($parentNodeId)->loadChildren();
            $prevNode = $tree->loadNode($prevNodeId);
            if ($prevNode->isEmpty()) {
                $prevNode = $parentNode->getLastChild();
            }
            
            $tree->moveNodeTo($node, $parentNode, $prevNode);
        }
        catch (Exception $e){
            $this->getResponse()->setBody(__('Category move error'));
        }
    }
    
    /**
     * Delete category action
     */
    public function deleteAction()
    {
        if ($id = (int) $this->getRequest()->getParam('id')) {
            try {
                Mage::getModel('catalog/category')->load($id)
                    ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess('Category deleted');
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError('Category delete error');
                $this->getResponse()->setRedirect(Mage::getUrl('*/*/edit', array('_current'=>true)));
                return;
            }
        }
        $this->getResponse()->setRedirect(Mage::getUrl('*/*/', array('_current'=>true, 'id'=>null)));
    }
    
    /**
     * Category save
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $category = Mage::getModel('catalog/category')
                ->setData($data)
                ->setId($this->getRequest()->getParam('id'))
                ->setStoreId(1)
                ->setAttributeSetId(12);
            try {
                $category->save();
                Mage::getSingleton('adminhtml/session')->addSuccess('Category saved');
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->getResponse()->setRedirect(Mage::getUrl('*/*/edit', array('_current'=>true)));
                return;
            }
        }

        $this->getResponse()->setRedirect(Mage::getUrl('*/*/edit', array('id'=>$category->getId())));
    }
}
