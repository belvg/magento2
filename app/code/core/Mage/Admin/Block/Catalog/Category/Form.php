<?php
/**
 * Category data form
 *
 * @package    Mage
 * @subpackage Admin
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 * @license    http://www.opensource.org/licenses/osl-3.0.php
 */
class Mage_Admin_Block_Catalog_Category_Form extends Mage_Core_Block_Form
{
    /**
     * Constructor
     *
     */
    public function __construct() 
    {
        parent::__construct();
        $this->setTemplate('form.phtml');
        $this->setAttribute('id', 'add_child_category_form');
        $this->setAttribute('legend', 'Category form');
        $this->setAttribute('class', 'x-form');
        $this->setAttribute('action', Mage::getBaseUrl().'admin/category/save/');
        
        $categoryId = (int) Mage::registry('controller')->getRequest()->getParam('catid', false);
        
        $this->addField('category_id', 'hidden', array('name'=>'category_id', 'value'=>$categoryId));
        $this->addField('attribute_set_id', 'hidden', array('name'=>'attribute_set_id', 'value'=>1));
        
        $attributes = Mage::getModel('catalog', 'category_attribute_set')
            ->setAttributeSetId(1)
            ->getAttributes();
        
            foreach ($attributes as $attribute) {
            $elementId      = $attribute->getCode();
            $elementType    = $attribute->getDataInput();
            
            $elementConfig  = array();
            $elementConfig['name'] = 'attribute['.$attribute->getId().']';
            $elementConfig['label']= $attribute->getCode();
            $elementConfig['id']   = $attribute->getCode();
            $elementConfig['value']= '';
            $elementConfig['title']= $attribute->getCode();
            $elementConfig['validation']= '';
            $elementConfig['ext_type']  = 'TextField';
            
            $this->addField($elementId, $elementType, $elementConfig);
        }
        
        if ($categoryId) {
            $category = Mage::getModel('catalog','category')->load($categoryId);
            $this->setElementsValues($category->getData());
        }
        //$this->addField('name', 'text', array('name'=>'name', 'id'=>'new_category_name', 'label'=>'Category name'));
    }
}