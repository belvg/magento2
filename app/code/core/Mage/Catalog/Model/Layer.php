<?php
/**
 * Catalog view layer model
 *
 * @package     Mage
 * @subpackage  Catalog
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Catalog_Model_Layer extends Varien_Object
{
    /**
     * Retrieve current layer product collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getProductCollection()
    {
        $collection = $this->getData('product_collection');
        if (is_null($collection)) {
            $collection = $this->getCurrentCategory()->getProductCollection()
                ->addCategoryFilter($this->getCurrentCategory());
            $this->prepareProductCollection($collection);
            $this->setData('product_collection', $collection);
        }
        
        return $collection;
    }
    
    public function prepareProductCollection($collection)
    {
        $collection->addAttributeToSelect('name')
        	->addAttributeToSelect('url_key')
            ->addAttributeToSelect('price')
            ->joinMinimalPrice()
            ->addAttributeToSelect('image')
            ->addAttributeToSelect('small_image')
            ->addAttributeToSelect('description')
            ->joinField('store_id', 
                'catalog/product_store', 
                'store_id', 
                'product_id=entity_id', 
                '{{table}}.store_id='.(int) $this->getCurrentStore()->getId());
                
        
        $collection->getEntity()->setStore((int) $this->getCurrentStore()->getId());
        $collection->addAttributeToFilter('status', array('in'=>$collection->getObject()->getVisibleInCatalogStatuses()));
        
        return $this;
    }
    
    /**
     * Retrieve current category model
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory()
    {
        $category = $this->getData('current_category');
        if (is_null($category)) {
            if ($category = Mage::registry('current_category')) {
                $this->setData('current_category', $category);
            }
            else {
                Mage::throwException('Can not retrieve current category object');
            }
        }
        return $category;
    }
    
    /**
     * Retrieve current store model
     *
     * @return Mage_Core_Model_Store
     */
    public function getCurrentStore()
    {
        return Mage::getSingleton('core/store');
    }
    
    public function getFilterableAttributes()
    {
        $entity = $this->getProductCollection()->getEntity();
        $collection = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter($entity->getConfig()->getId())
            ->addIsFilterableFilter()
            ->load();
        foreach ($collection as $item) {
        	$item->setEntity($entity);
        }
        
        return $collection;
    }
    
    /**
     * Retrieve layer state object
     *
     * @return Mage_Catalog_Model_Layer_State
     */
    public function getState()
    {
        $state = $this->getData('state');
        if (is_null($state)) {
            $state = Mage::getModel('catalog/layer_state');
            $this->setData('state', $state);
        }
        return $state;
    }
}
