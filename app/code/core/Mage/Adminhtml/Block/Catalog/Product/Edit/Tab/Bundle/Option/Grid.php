<?php
/**
 * Adminhtml catalog product option links grid
 *
 * @package    Mage
 * @subpackage Adminhtml
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 * @license    http://www.opensource.org/licenses/osl-3.0.php
 * @author	   Ivan Chepurnyi <mitch@varien.com>
 */

class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Bundle_Option_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct() 
	{
		parent::__construct();
		$this->setDefaultFilter(array('in_products'=>1));
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
		$this->setId($this->getRequest()->getParam('gridId'));
	}
	
	protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
            	$this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            }
            else {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
	
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('products', null);
                        
        if (!is_array($products)) {
            $products = null;
        }        
        return $products;
    }
    
    protected function _prepareCollection()
    {
       
        $option = Mage::getModel('catalog/product_bundle_option')
        	->load($this->getRequest()->getParam('option', 0));
        
       	if(!$option->getId()) {
       		$option->setStoreId(Mage::registry('product')->getStoreId());
       	}
       	
       	$collection = $option->getLinkCollection()
       		->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->useProductItem();

        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }
    
    public function toHtml() 
    {
		$result = parent::toHtml();
		if($this->canDisplayContainer()) {
			$result.= '<script type="text/javascript"><!--'."\n"
					. $this->getRequest()->getParam('jsController') . '.initGrid(' . (int)$this->getRequest()->getParam('index') 
					. ', ' . $this->getJsObjectName() . ');' . "\n"
				    . '//--></script>';
		}
		
		return $result;
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_products',
            'values'    => $this->_getSelectedProducts(),
            'align'     => 'center',
            'index'     => 'entity_id'
        ));
        
        $this->addColumn('id', array(
            'header'    => __('ID'),
            'sortable'  => true,
            'width'     => '60px',
            'index'     => 'entity_id'
        ));
        $this->addColumn('name', array(
            'header'    => __('Name'),
            'index'     => 'name'
        ));
        $this->addColumn('sku', array(
            'header'    => __('SKU'),
            'width'     => '80px',
            'index'     => 'sku'
        ));
        $this->addColumn('price', array(
            'header'    => __('Price'),
            'align'     => 'center',
            'type'      => 'currency',
            'index'     => 'price'
        ));
                        
        $this->addColumn('discount', array(
            'header'    => __('Discount'),
            'name'    	=> 'discount',
            'align'     => 'center',
            'type'      => 'number',
            'validate_class' => 'validate-number',
            'index'     => 'discount',
            'width'     => '60px',
            'editable'  => true
        ));
        
         
        
        return parent::_prepareColumns();
    }
}// Class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Bundle_Option_Grid END