<?php
/**
 * Adminhtml reviews by products report grid block
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Dmytro Vasylenko <dimav@varien.com>
 */
class Mage_Adminhtml_Block_Report_Tag_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('gridProducts');
    }

    protected function _prepareCollection()
    {     
        
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('name');
        
        $collection->getSelect()
            ->joinRight(array('tr' => 'tag_relation'), 'tr.product_id=e.entity_id', array('taged' => 'count(DISTINCT(tr.tag_id))'))
            ->joinRight(array('t' => 'tag'), 't.tag_id=tr.tag_id', 'status')
            ->where('t.status='.Mage_Tag_Model_Tag::STATUS_APPROVED)
            ->group('tr.product_id')
            ->order('taged DESC');     
        
        //echo $collection->getSelect()->__toString();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        
        $this->addColumn('entity_id', array(
            'header'    =>__('ID'),
            'width'     =>'50px',
            'index'     =>'entity_id'
        ));
        
        $this->addColumn('name', array(
            'header'    =>__('Product Name'),
            'width'     =>'200px',
            'index'     =>'name'
        ));    
        
        $this->addColumn('taged', array(
            'header'    =>__('Number of Unique Tags'),
            'width'     =>'40px',
            'index'     =>'taged'
        ));
         
        $this->setFilterVisibility(false); 
                      
        return parent::_prepareColumns();
    }
       
    public function getRowUrl($row)
    {
        return Mage::getUrl('*/*/productDetail', array('id'=>$row->entity_id));
    }
    
}
