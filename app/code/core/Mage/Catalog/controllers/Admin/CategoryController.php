<?php
/**
 * Admin category controller
 *
 * @package    Ecom
 * @subpackage Catalog
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 */
class Mage_Catalog_CategoryController extends Mage_Core_Controller_Admin_Action
{
    /**
     * New category layout
     *
     */
    public function newAction()
    {
        $form = Mage::createBlock('admin_catalog_category_form', 'category_form');
        $this->getResponse()->setBody($form->toString());
    }
    
    public function formAction()
    {
        $form = Mage::createBlock('admin_catalog_category_form', 'category_form');
        $this->getResponse()->setBody($form->toString());
    }
    
    public function saveAction()
    {
        echo 'C save';
    }

    public function removeAction() {
    }

    public function moveAction() {
    }

    /**
     * Category tree json
     *
     */
    public function treeChildrenAction()
    {
        $tree = Mage::getModel('catalog_resource','category_tree');
        $parentNodeId = (int) $this->getRequest()->getPost('node',1);
        $websiteId = (int) $this->getRequest()->getPost('website',1);

        $nodes = $tree->setWebsiteId($websiteId)
                    ->joinAttribute('name')
                    ->loadNode($parentNodeId)
                        ->loadChildren(1)
                        ->getChildren();

        $items = array();
        foreach ($nodes as $node) {
            $item = array();
            $item['text']= $node->getName(); //.'(id #'.$child->getId().')';
            $item['id']  = $node->getId();
            $item['cls'] = 'folder';
            if (!$node->hasChildren()) {
                $item['leaf'] = 'true';    
            }
            $items[] = $item;
        }

        $this->getResponse()->setBody(Zend_Json::encode($items));
    }
    
    public function treeWebsiteAction()
    {
            $websiteId = (int) $this->getRequest()->getParam('website', false);
            if ($websiteId) {
                $website = Mage::getModel('core', 'website')->load($websiteId);
            }
            else {
                $website = Mage::getModel('core', 'website')->setRootCategoryId(1);
            }
            
            $item = array(
                'text'  => __('Catalog categories'),
                'id'    => $website->getRootCategoryId(),
                'cls'   => 'folder',
                'expanded' => 'true'
            );
            $this->getResponse()->setBody(Zend_Json::encode(array($item)));
    }

    //Category attributes
    public function attributesSetGridDataAction()
    {
        /*$block = Mage::createBlock('tpl', 'category_attributes_grid');
        $block->setTemplate('catalog/Admin/category/attributes_set_grid.phtml');
        $this->getResponse()->setBody($block->toString());*/
    }
    
    public function attributesGridAction()
    {
        echo 'atttibutes';
    }
    
    public function arrtibutesSetTreeAction()
    {
        echo 'tree';
    }
}
