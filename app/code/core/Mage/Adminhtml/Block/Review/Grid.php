<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml reviews grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Alexander Stadnitski <alexander@varien.com>
 */
class Mage_Adminhtml_Block_Review_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('reviwGrid');
        $this->setDefaultSort('created_at');
    }

    protected function _prepareCollection()
    {
        $model = Mage::getModel('review/review');
        $collection = $model->getProductCollection();

        if( $this->getProductId() || $this->getRequest()->getParam('productId', false) ) {
            $this->setProductId( ( $this->getProductId() ? $this->getProductId() : $this->getRequest()->getParam('productId') ) );
            $collection->addEntityFilter($this->getProductId());
        }

        if( $this->getCustomerId() || $this->getRequest()->getParam('customerId', false) ) {
            $this->setCustomerId( ( $this->getCustomerId() ? $this->getCustomerId() : $this->getRequest()->getParam('customerId') ) );
            $collection->addCustomerFilter($this->getCustomerId());
        }

        if( Mage::registry('usePendingFilter') === true ) {
            $collection->addStatusFilter($model->getPendingStatus());
        }

        $collection->addStoreData();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $statuses = Mage::getModel('review/review')
            ->getStatusCollection()
            ->load()
            ->toOptionArray();

        foreach( $statuses as $key => $status ) {
            $tmpArr[$status['value']] = $status['label'];
        }

        $statuses = $tmpArr;

        $this->addColumn('review_id', array(
            'header'        => Mage::helper('review')->__('ID'),
            'align'         => 'right',
            'width'         => '50px',
            'filter_index'  => 'rt.review_id',
            'index'         => 'review_id',
        ));

        $this->addColumn('created_at', array(
            'header'        => Mage::helper('review')->__('Created On'),
            'align'         => 'left',
            'type'          => 'datetime',
            'width'         => '100px',
            'filter_index'  => 'rt.created_at',
            'index'         => 'created_at',
        ));

        if( !Mage::registry('usePendingFilter') ) {
            $this->addColumn('status', array(
                'header'        => Mage::helper('review')->__('Status'),
                'align'         => 'left',
                'type'          => 'options',
                'options'       => $statuses,
                'width'         => '100px',
                'filter_index'  => 'rt.status_id',
                'index'         => 'status_id',
            ));
        }

        $this->addColumn('title', array(
            'header'        => Mage::helper('review')->__('Title'),
            'align'         => 'left',
            'width'         => '100px',
            'filter_index'  => 'rdt.title',
            'index'         => 'title',
        ));

        $this->addColumn('nickname', array(
            'header'        => Mage::helper('review')->__('Nickname'),
            'align'         => 'left',
            'width'         => '100px',
            'filter_index'  => 'rdt.nickname',
            'index'         => 'nickname',
        ));

        $this->addColumn('detail', array(
            'header'        => Mage::helper('review')->__('Review'),
            'align'         => 'left',
            'type'          => 'text',
            'index'         => 'detail',
            'filter_index'  => 'rdt.detail',
            'renderer'      => 'adminhtml/review_grid_renderer_detail'
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('visible_in', array(
                'header'    => Mage::helper('review')->__('Visible In'),
                'type'      => 'select',
                'index'     => 'stores',
                'type'      => 'store'
            ));
        }

        $this->addColumn('type', array(
            'header'    => Mage::helper('review')->__('Type'),
            'type'      => 'select',
            'index'     => 'type',
            'filter'    => 'adminhtml/review_grid_filter_type',
            'renderer'  => 'adminhtml/review_grid_renderer_type'
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('review')->__('Product Name'),
            'align'     =>'left',
            'type'      => 'text',
            'index'     => 'name',
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('review')->__('Product SKU'),
            'align'     => 'right',
            'type'      => 'text',
            'width'     => '50px',
            'index'     => 'sku',
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('adminhtml')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('adminhtml')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit',
                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
        ));

        $this->addRssList('rss/catalog/review', Mage::helper('catalog')->__('Pending Reviews RSS'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        if (Mage::registry('usePendingFilter') == true) {
            $this->setMassactionIdField('review_id');
            $this->setMassactionIdFieldOnlyIndexValue(true);
            $this->getMassactionBlock()->setFormFieldName('reviews');

            $this->getMassactionBlock()->addItem('delete', array(
                'label'=> Mage::helper('review')->__('Delete'),
                'url'  => $this->getUrl('*/*/massDelete'),
                'confirm' => Mage::helper('review')->__('Are you sure?')
            ));

            $statuses = Mage::getModel('review/review')
                ->getStatusCollection()
                ->load()
                ->toOptionArray();
            array_unshift($statuses, array('label'=>'', 'value'=>''));
            $this->getMassactionBlock()->addItem('update_status', array(
                'label'         => Mage::helper('review')->__('Update status'),
                'url'           => $this->getUrl('*/*/massUpdateStatus'),
                'additional'    => array(
                    'status'    => array(
                        'name'      => 'status',
                        'type'      => 'select',
                        'class'     => 'required-entry',
                        'label'     => Mage::helper('review')->__('Status'),
                        'values'    => $statuses
                    )
                )
            ));

            /**
             * Check is single store mode
             */
            if (!Mage::app()->isSingleStoreMode()) {
                $this->getMassactionBlock()->addItem('visible_in', array(
                    'label'         => Mage::helper('review')->__('Set visible in'),
                    'url'           => $this->getUrl('*/*/massVisibleIn'),
                    'additional'    => array(
                        'status'    => array(
                            'name'      => 'stores',
                            'type'      => 'multiselect',
                            'class'     => 'required-entry',
                            'label'     => Mage::helper('review')->__('Store(s)'),
                            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
                        )
                    )
                ));
            }
        }
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/catalog_product_review/edit', array(
            'id' => $row->getReviewId(),
            'productId' => $this->getProductId(),
            'customerId' => $this->getCustomerId(),
            'ret'       => ( Mage::registry('usePendingFilter') ) ? 'pending' : null,
        ));
    }

    public function getGridUrl()
    {
        if( $this->getProductId() || $this->getCustomerId() ) {
            return $this->getUrl('*/catalog_product_review/reviewGrid', array(
                'productId' => $this->getProductId(),
                'customerId' => $this->getCustomerId(),
            ));
        } else {
            return $this->getCurrentUrl();
        }
    }
}
