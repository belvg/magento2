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
 * Poll answers grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Poll_Edit_Tab_Answers_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('answersGrid');
        $this->setDefaultSort('answer_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Mage_Poll_Model_Poll_Answer')
            ->getResourceCollection()
            ->addPollFilter($this->getRequest()->getParam('id'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('answer_id', array(
            'header'    => Mage::helper('Mage_Poll_Helper_Data')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'answer_id',
        ));

        $this->addColumn('answer_title', array(
            'header'    => Mage::helper('Mage_Poll_Helper_Data')->__('Answer Title'),
            'align'     =>'left',
            'index'     => 'answer_title',
        ));

        $this->addColumn('votes_count', array(
            'header'    => Mage::helper('Mage_Poll_Helper_Data')->__('Votes'),
            'type'      => 'number',
            'width'     => '50px',
            'index'     => 'votes_count',
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('Mage_Poll_Helper_Data')->__('Actions'),
            'align'     => 'center',
            'type'      => 'action',
            'width'     => '10px',
            'filter'    => false,
            'sortable'  => false,
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('Mage_Poll_Helper_Data')->__('Delete'),
                    'onClick'   => 'return answers.delete(\'$answer_id\')',
                    'url'       => '#',
                ),
            ),
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/poll_answer/edit', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/poll_answer/grid', array('id' => $this->getRequest()->getParam('id')));
    }

}
