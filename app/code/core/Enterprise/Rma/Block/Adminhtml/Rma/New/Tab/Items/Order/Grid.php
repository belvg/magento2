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
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Admin RMA create order grid block
 *
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 */

class Enterprise_Rma_Block_Adminhtml_Rma_New_Tab_Items_Order_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Variable to store store-depended string values of attributes
     *
     * @var null|array
     */
    protected $_attributeOptionValues = null;

    /**
     * Block constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('order_items_grid');
        $this->setDefaultSort('item_id');
        $this->setUseAjax(true);
        $this->setPagerVisibility(false);
    }

    /**
     * Prepare grid collection object
     *
     * @return Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_Items_Grid
     */
    protected function _prepareCollection()
    {
        $orderId = Mage::registry('current_order')->getId();

        /** @var $collection Enterprise_Rma_Model_Resource_Item */

        $orderItemsCollection = Mage::getResourceModel('enterprise_rma/item')->getOrderItemsCollection($orderId);

        $this->setCollection($orderItemsCollection);

        return parent::_prepareCollection();
    }

    /**
     * After load collection processing.
     *
     * Filter items collection due to RMA needs. Remove forbidden items, non-applicable
     * bundles (and their children) and configurables
     *
     * @return Enterprise_Rma_Block_Adminhtml_Rma_New_Tab_Items_Order_Grid
     */
    protected function _afterLoadCollection()
    {
        $orderId = Mage::registry('current_order')->getId();
        $itemsInActiveRmaArray = Mage::getResourceModel('enterprise_rma/item')->getItemsIdsByOrder($orderId);

        /**
         * contains data that defines possibility of return for an order item
         * array value ['self'] refers to item's own rules
         * array value ['child'] refers to rules defined from item's sub-items
         */
        $parent = array();

        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product');

        foreach ($this->getCollection() as $item) {
            $allowed = true;
            if (in_array($item->getId(), $itemsInActiveRmaArray)) {
                $allowed = false;
            }

            if ($allowed === true) {
                $product->reset();
                $product->setStoreId($item->getStoreId());
                $product->load($item->getProductId());

                if (!Mage::helper('enterprise_rma')->canReturnProduct($product, $item->getStoreId())) {
                    $allowed = false;
                }
            }

            if ($item->getParentItemId()) {
                if (!isset($parent[$item->getParentItemId()]['child'])) {
                    $parent[$item->getParentItemId()]['child'] = false;
                }
                $parent[$item->getParentItemId()]['child']  = $parent[$item->getParentItemId()]['child'] || $allowed;
                $parent[$item->getItemId()]['self']         = false;
            } else {
                $parent[$item->getItemId()]['self']         = $allowed;
            }
        }

        foreach ($this->getCollection() as $item) {
            if (isset($parent[$item->getId()]['self']) && $parent[$item->getId()]['self'] === false) {
                $this->getCollection()->removeItemByKey($item->getId());
                continue;
            }
            if (isset($parent[$item->getId()]['child']) && $parent[$item->getId()]['child'] === false) {
                $this->getCollection()->removeItemByKey($item->getId());
                continue;
            }
            if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE
                && !isset($parent[$item->getId()]['child'])
            ) {
                $this->getCollection()->removeItemByKey($item->getId());
                continue;
            }

            if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $productOptions     = $item->getProductOptions();
                $product->reset();
                $product->load($product->getIdBySku($productOptions['simple_sku']));
                if (!Mage::helper('enterprise_rma')->canReturnProduct($product, $item->getStoreId())) {
                    $this->getCollection()->removeItemByKey($item->getId());
                    continue;
                }
            }

            $item->setName(Mage::helper('enterprise_rma')->getAdminProductName($item));
        }

        return $this;
    }

    /**
     * Prepare columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('select', array(
            'header'=> Mage::helper('enterprise_rma')->__('Select'),
            'width' => '40px',
            'type'  => 'checkbox',
            'align'     => 'center',
            'sortable' => false,
            'index' => 'item_id',
        ));

        $this->addColumn('product_name', array(
            'header'    => Mage::helper('enterprise_rma')->__('Product Name'),
            'renderer'  => 'enterprise_rma/adminhtml_product_bundle_product',
            'index'     => 'name'
        ));

        $this->addColumn('sku', array(
            'header'=> Mage::helper('enterprise_rma')->__('SKU'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'sku',
        ));

        $this->addColumn('price', array(
            'header'=> Mage::helper('enterprise_rma')->__('Price'),
            'width' => '80px',
            'type'  => 'currency',
            'index' => 'price',
        ));

        $this->addColumn('available_qty', array(
            'header'=> Mage::helper('enterprise_rma')->__('Remaining Qty'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'available_qty',
            'renderer'  => 'enterprise_rma/adminhtml_rma_edit_tab_items_grid_column_renderer_quantity',
            'filter' => false,
            'sortable' => false,
        ));

        return parent::_prepareColumns();
    }

    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        $js = '
            function (grid, event) {
                var trElement = Event.findElement(event, \'tr\');
                var isInput = Event.element(event).tagName == \'INPUT\';
                if (trElement) {
                    var checkbox = Element.select(trElement, \'input\');
                    if (checkbox[0]) {
                        var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                        grid.setCheckboxChecked(checkbox[0], checked);
                    }
                    var link = Element.select(trElement, \'a[class="product_to_add"]\');
                    if (link[0]) {
                        rma.showBundleItems(event)
                    }
                }
            }
        ';
        return $js;
    }

    /**
     * Get Url to action to reload grid
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/addProductGrid', array('_current' => true));
    }

}