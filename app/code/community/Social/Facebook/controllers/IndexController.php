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
 * @category    Social
 * @package     Social_Facebook
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Social_Facebook_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action For Facebook Action Redirect
     */
    public function redirectAction()
    {
        $link   = $this->_facebookRedirect();
        if (!$link) {
            return;
        }

        $this->_redirectUrl($link);
        return;
    }

    /**
     * Get Facebook Redirect For Current Action
     *
     * @return string
     */
    private function _facebookRedirect()
    {
        $session    = Mage::getSingleton('core/session');
        $action     = $this->getRequest()->getParam('action');
        $productId  = $this->getRequest()->getParam('productId');
        $product    = Mage::getModel('Mage_Catalog_Model_Product')->load($productId);
        $productUrl = $product->getUrlModel()->getUrlInStore($product);

        $session->setData('product_id', $productId);
        $session->setData('product_url', $productUrl);
        $session->setData('product_og_url', Mage::getUrl('facebook/index/page', array('id' => $productId)));
        $session->setData('facebook_action', $action);

        if ($session->getData('access_token') && $this->_checkAnswer() && $action) {
            $this->_redirectUrl($productUrl);
            return;
        }

        return Mage::helper('Social_Facebook_Helper_Data')->getRedirectUrl($product);
    }

    /**
     * Check is Facebook Token Alive
     *
     * @return bool
     */
    protected function _checkAnswer()
    {
        if (Mage::getSingleton('social_facebook/facebook')->getFacebookUser()) {
            return true;
        }

        return false;
    }

    /**
     * Get Metatags for Facebook
     */
    public function pageAction()
    {
        $productId = (int)$this->getRequest()->getParam('id');
        if ($productId) {
            $product = Mage::getModel('Mage_Catalog_Model_Product')->load($productId);

            if ($product->getId()) {
                Mage::register('product', $product);
            }

            $this->loadLayout();
            $response = $this->getLayout()->createBlock('Social_Facebook_Block_Head')->toHtml();
            $this->getResponse()->setBody($response);
        }
    }
}
