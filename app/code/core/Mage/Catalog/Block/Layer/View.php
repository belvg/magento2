<?php
/**
 * Catalog layered navigation view block
 *
 * @package     Mag
 * @subpackage  Catalog
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Catalog_Block_Layer_View extends Mage_Core_Block_Template
{
    public function __construct() 
    {
        parent::__construct();
        $this->setTemplate('catalog/layer/view.phtml');
    }
    
    public function _initChildren()
    {
        $this->setChild('category_filter',
            $this->getLayout()->createBlock('catalog/layer_filter_category'));
        $this->setChild('price_filter',
            $this->getLayout()->createBlock('catalog/layer_filter_price'));
        
        $filterableAttributes = $this->_getFilterableAttributes();
        foreach ($filterableAttributes as $attribute) {
        	$this->setChild($attribute->getAttributeCode().'_filter',
                $this->getLayout()->createBlock('catalog/layer_filter_attribute',
                    '',
                    array('attribute_model'=>$attribute)
                ));
        }
    }
    
    public function getFilters()
    {
        $filters = array();
        if ($categoryFilter = $this->_getCategoryFilter()) {
            $filters[] = $categoryFilter;
        }

        if ($priceFilter = $this->_getPriceFilter()) {
            $filters[] = $priceFilter;
        }
        
        $filterableAttributes = $this->_getFilterableAttributes();
        foreach ($filterableAttributes as $attribute) {
        	$filters[] = $this->getChild($attribute->getAttributeCode().'_filter');
        }
        return $filters;
    }
    
    protected function _getCategoryFilter()
    {
        return $this->getChild('category_filter');
    }
    
    protected function _getPriceFilter()
    {
        return $this->getChild('price_filter');
    }
    
    protected function _getAttributeFilters()
    {
        
    }
    
    protected function _getFilterableAttributes()
    {
        $attributes = $this->getData('_filterable_attributes');
        if (is_null($attributes)) {
            $attributes = Mage::getSingleton('catalog/layer')->getFilterableAttributes();
            $this->setData('_filterable_attributes', $attributes);
        }
        return $attributes;
    }
}
