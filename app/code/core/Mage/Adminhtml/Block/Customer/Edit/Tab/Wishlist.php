<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Adminhtml customer orders grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_Wishlist extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Default sort field
     *
     * @var string
     */

    protected $_defaultSort = 'added_at';

    /**
     * Parent template name
     *
     * @var string
     */
    protected $_parentTemplate;

    /**
     * List of helpers to show options for product cells
     */
    protected $_productHelpers = array();

    /**
     * Initialize Grid
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('wishlistGrid');
        $this->setUseAjax(true);
        $this->_parentTemplate = $this->getTemplate();
        $this->setTemplate('Mage_Adminhtml::customer/tab/wishlist.phtml');
        $this->setEmptyText(Mage::helper('Mage_Customer_Helper_Data')->__('No Items Found'));
        $this->addProductConfigurationHelper('default', 'Mage_Catalog_Helper_Product_Configuration');
    }

    /**
     * Retrieve current customer object
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer()
    {
        return Mage::registry('current_customer');
    }

    /**
     * Create customer wishlist item collection
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    protected function _createCollection()
    {
        return Mage::getModel('Mage_Wishlist_Model_Item')->getCollection();
    }

    /**
     * Prepare customer wishlist product collection
     *
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Wishlist
     */
    protected function _prepareCollection()
    {
        $collection = $this->_createCollection()->addCustomerIdFilter($this->_getCustomer()->getId())
            ->resetSortOrder()
            ->addDaysInWishlist()
            ->addStoreData();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare Grid columns
     *
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Wishlist
     */
    protected function _prepareColumns()
    {
        $this->addColumn('product_name', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Product name'),
            'index'     => 'product_name',
            'renderer'  => 'Mage_Adminhtml_Block_Customer_Edit_Tab_View_Grid_Renderer_Item'
        ));

        $this->addColumn('description', array(
            'header'    => Mage::helper('Mage_Wishlist_Helper_Data')->__('User description'),
            'index'     => 'description',
            'renderer'  => 'Mage_Adminhtml_Block_Customer_Edit_Tab_Wishlist_Grid_Renderer_Description'
        ));

        $this->addColumn('qty', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Qty'),
            'index'     => 'qty',
            'type'      => 'number',
            'width'     => '60px'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store', array(
                'header'    => Mage::helper('Mage_Wishlist_Helper_Data')->__('Added From'),
                'index'     => 'store_id',
                'type'      => 'store',
                'width'     => '160px'
            ));
        }

        $this->addColumn('added_at', array(
            'header'    => Mage::helper('Mage_Wishlist_Helper_Data')->__('Date Added'),
            'index'     => 'added_at',
            'gmtoffset' => true,
            'type'      => 'date'
        ));

        $this->addColumn('days', array(
            'header'    => Mage::helper('Mage_Wishlist_Helper_Data')->__('Days in Wishlist'),
            'index'     => 'days_in_wishlist',
            'type'      => 'number'
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Action'),
            'index'     => 'wishlist_item_id',
            'renderer'  => 'Mage_Adminhtml_Block_Customer_Grid_Renderer_Multiaction',
            'filter'    => false,
            'sortable'  => false,
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('Mage_Customer_Helper_Data')->__('Configure'),
                    'url'       => 'javascript:void(0)',
                    'process'   => 'configurable',
                    'control_object' => 'wishlistControl'
                ),
                array(
                    'caption'   => Mage::helper('Mage_Customer_Helper_Data')->__('Delete'),
                    'url'       => '#',
                    'onclick'   => 'return wishlistControl.removeItem($wishlist_item_id);'
                )
            )
        ));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve Grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/wishlist', array('_current'=>true));
    }

    /**
     * Add column filter to collection
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Wishlist
     */
    protected function _addColumnFilterToCollection($column)
    {
        /* @var $collection Mage_Wishlist_Model_Resource_Item_Collection */
        $collection = $this->getCollection();
        $value = $column->getFilter()->getValue();
        if ($collection && $value) {
            switch ($column->getId()) {
                case 'product_name':
                    $collection->addProductNameFilter($value);
                    break;
                case 'store':
                    $collection->addStoreFilter($value);
                    break;
                case 'days':
                    $collection->addDaysFilter($value);
                    break;
                default:
                    $collection->addFieldToFilter($column->getIndex(), $column->getFilter()->getCondition());
                    break;
            }
        }
        return $this;
    }

    /**
     * Sets sorting order by some column
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Wishlist
     */
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            switch ($column->getId()) {
                case 'product_name':
                    $collection->setOrderByProductName($column->getDir());
                    break;
                default:
                    parent::_setCollectionOrder($column);
                    break;
            }
        }
        return $this;
    }

    /**
     * Retrieve Grid Parent Block HTML
     *
     * @return string
     */
    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    /**
     * Retrieve Row click URL
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/catalog_product/edit', array('id' => $row->getProductId()));
    }

    /**
     * Adds product type helper depended on product type (used to show options in item cell)
     *
     * @param string $productType
     * @param string $helperName
     *
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_Wishlist
     */
    public function addProductConfigurationHelper($productType, $helperName)
    {
        $this->_productHelpers[$productType] = $helperName;
        return $this;
    }

    /**
     * Returns array of product configuration helpers
     *
     * @return array
     */
    public function getProductConfigurationHelpers()
    {
        return $this->_productHelpers;
    }
}
