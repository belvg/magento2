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
 * @package    Mage_Rss
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer reviews controller
 *
 * @category   Mage
 * @package    Mage_Rss
 * @author     Lindy Kyaw <lindy@varien.com>
 */

class Mage_Rss_OrderController extends Mage_Core_Controller_Front_Action
{
    public function newAction()
    {
        if (Mage::app()->getStore()->isCurrentlySecure()) {
            Mage::helper('rss')->authAdmin();
            $this->loadLayout(false);
            $this->renderLayout();
        } else {
            $this->_redirect('rss/order/new', array('_secure'=>true));
            return $this;
        }
    }

    public function customerAction()
    {
        if (Mage::app()->getStore()->isCurrentlySecure()) {
            Mage::helper('rss')->authFrontend();
        } else {
            $this->_redirect('rss/order/customer', array('_secure'=>true));
            return $this;
        }
    }

    public function statusAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

}