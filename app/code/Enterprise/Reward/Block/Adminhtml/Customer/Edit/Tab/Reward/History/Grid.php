<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Reward History grid
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Block_Adminhtml_Customer_Edit_Tab_Reward_History_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Internal constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
        $this->setId('rewardPointsHistoryGrid');
    }

    /**
     * Prepare grid collection object
     *
     * @return Enterprise_Reward_Block_Adminhtml_Customer_Edit_Tab_Reward_History_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection Enterprise_Reward_Model_Resource_Reward_History_Collection */
        $collection = Mage::getModel('Enterprise_Reward_Model_Reward_History')->getCollection()
            ->addCustomerFilter($this->getCustomerId())
            ->setExpiryConfig(Mage::helper('Enterprise_Reward_Helper_Data')->getExpiryConfig())
            ->addExpirationDate()
            ->setOrder('history_id', 'desc');
        $collection->setDefaultOrder();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add column filter to collection
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Enterprise_Reward_Block_Adminhtml_Customer_Edit_Tab_Reward_History_Grid
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
            if ($field == 'website_id' || $field == 'points_balance') {
                $cond = $column->getFilter()->getCondition();
                if ($field && isset($cond)) {
                    $this->getCollection()->addFieldToFilter('main_table.'.$field , $cond);
                }
            } else {
                parent::_addColumnFilterToCollection($column);
            }
        }
        return $this;
    }

    /**
     * Prepare grid columns
     *
     * @return Mage_Widget_Block_Adminhtml_Widget_Instance_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('points_balance', array(
            'type'     => 'number',
            'index'    => 'points_balance',
            'header'   => Mage::helper('Enterprise_Reward_Helper_Data')->__('Balance'),
            'sortable' => false,
            'filter'   => false,
            'width'    => 1,
        ));

        $this->addColumn('currency_amount', array(
            'type'     => 'currency',
            'currency' => 'base_currency_code',
            'rate'     => 1,
            'index'    => 'currency_amount',
            'header'   => Mage::helper('Enterprise_Reward_Helper_Data')->__('Amount Balance'),
            'sortable' => false,
            'filter'   => false,
            'width'    => 1,
        ));

        $this->addColumn('points_delta', array(
            'type'     => 'number',
            'index'    => 'points_delta',
            'header'   => Mage::helper('Enterprise_Reward_Helper_Data')->__('Points'),
            'sortable' => false,
            'filter'   => false,
            'show_number_sign' => true,
            'width'    => 1,
        ));

        $this->addColumn('currency_delta', array(
            'type'     => 'currency',
            'currency' => 'base_currency_code',
            'rate'     => 1,
            'index'    => 'currency_delta',
            'header'   => Mage::helper('Enterprise_Reward_Helper_Data')->__('Amount'),
            'sortable' => false,
            'filter'   => false,
            'show_number_sign' => true,
            'width'    => 1,
        ));

        $this->addColumn('rate', array(
            'getter' => 'getRateText',
            'header'   => Mage::helper('Enterprise_Reward_Helper_Data')->__('Rate'),
            'sortable' => false,
            'filter'   => false
        ));

// TODO: instead of source models move options to a getter
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website', array(
                'type'     => 'options',
                'options'  => Mage::getModel('Enterprise_Reward_Model_Source_Website')->toOptionArray(false),
                'index'    => 'website_id',
                'header'   => Mage::helper('Enterprise_Reward_Helper_Data')->__('Website'),
                'sortable' => false,
            ));
        }

// TODO: custom renderer for reason, which includes comments
        $this->addColumn('message', array(
            'index'    => 'message',
            'type'     => 'text',
            'getter'   => 'getMessage',
            'header'   => Mage::helper('Enterprise_Reward_Helper_Data')->__('Reason'),
            'sortable' => false,
            'filter'   => false,
            'renderer' =>
                'Enterprise_Reward_Block_Adminhtml_Customer_Edit_Tab_Reward_History_Grid_Column_Renderer_Reason',
        ));

        $this->addColumn('created_at', array(
            'type'     => 'datetime',
            'index'    => 'created_at',
            'header'   => Mage::helper('Enterprise_Reward_Helper_Data')->__('Created At'),
            'sortable' => false,
            'align'    => 'left',
            'html_decorators' => 'nobr',
        ));

        $this->addColumn('expiration_date', array(
            'type'     => 'datetime',
            'getter'   => 'getExpiresAt',
            'header'   => Mage::helper('Enterprise_Reward_Helper_Data')->__('Expires At'),
            'sortable' => false,
            'filter'   => false, // needs custom filter
            'align'    => 'left',
            'html_decorators' => 'nobr',
        ));

// TODO: merge with reason
        $this->addColumn('comment', array(
            'index'    => 'comment',
            'header'   => Mage::helper('Enterprise_Reward_Helper_Data')->__('Comment'),
            'sortable' => false,
            'filter'   => false,
            'align'    => 'left',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Return grid url for ajax actions
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/historyGrid', array('_current' => true));
    }

    /**
     * Return grid row url
     *
     * @param Enterprise_Reward_Model_Reward_History $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }
}