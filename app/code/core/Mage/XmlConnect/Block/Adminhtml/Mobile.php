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
 * @category    Mage
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_XmlConnect_Block_Adminhtml_Mobile extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_mobile';
        $this->_blockGroup = 'xmlconnect';
        $this->_headerText = Mage::helper('xmlconnect')->__('Manage Applications');
        $this->_addButtonLabel = Mage::helper('xmlconnect')->__('Add Application');

        parent::__construct();

        $this->addButton('save_and_edit_button', array(
            'label'     => Mage::helper('tag')->__('Create App for All Stores and Devices'),
            'onclick'   => "setLocation('" . $this->getUrl('*/*/fill') . "')",
            'sort_order' => '1',
        ), 1);
    }

}
