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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml invitation general report grid block
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Enterprise_Invitation_Block_Adminhtml_Report_Invitation_General_Grid
    extends Mage_Adminhtml_Block_Report_Grid
{

    /**
     * Prepare report collection
     *
     * @return Enterprise_Invitation_Block_Adminhtml_Report_Invitation_General_Grid
     */
    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $this->getCollection()->initReport('enterprise_invitation/report_invitation_collection');
        return $this;
    }

    /**
     * Prepare report grid columns
     *
     * @return Enterprise_Invitation_Block_Adminhtml_Report_Invitation_General_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('sent', array(
            'header'    =>Mage::helper('enterprise_invitation')->__('Sent'),
            'type'      =>'number',
            'index'     => 'sent'
        ));

        $this->addColumn('accepted', array(
            'header'    =>Mage::helper('enterprise_invitation')->__('Accepted'),
            'type'      =>'number',
            'index'     => 'accepted',
            'width'     => ''
        ));

        $this->addColumn('canceled', array(
            'header'    => Mage::helper('enterprise_invitation')->__('Canceled'),
            'type'      =>'number',
            'index'     => 'canceled',
            'width'     => ''
        ));

        $this->addColumn('accepted_rate', array(
            'header'    =>Mage::helper('enterprise_invitation')->__('Acceptance Conversion Rate'),
            'index'     =>'accepted_rate',
            'renderer'  => 'invitation/grid_adminhtml_column_renderer_percent',
            'type'      =>'string',
            'width'     => '170'

        ));

        $this->addColumn('canceled_rate', array(
            'header'    =>Mage::helper('enterprise_invitation')->__('Canceled Conversion Rate'),
            'index'     =>'canceled_rate',
            'type'      =>'number',
            'renderer'  => 'invitation/grid_adminhtml_column_renderer_percent',
            'width'     => '170'
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('enterprise_invitation')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('enterprise_invitation')->__('Excel'));

        return parent::_prepareColumns();
    }


}