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


abstract class Mage_XmlConnect_Controller_Action extends Mage_Core_Controller_Front_Action
{
    const MESSAGE_STATUS_ERROR      = 'error';
    const MESSAGE_STATUS_WARNING    = 'warning';
    const MESSAGE_STATUS_SUCCESS    = 'success';

    const MESSAGE_TYPE_ALERT        = 'alert';
    const MESSAGE_TYPE_PROMPT       = 'prompt';

    /**
     * Declare content type header
     * Validate current application
     */
    public function preDispatch()
    {
        parent::preDispatch();
        
        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');

        /**
         * Load application by specified code and make sure that application exists
         */
        $cookieName = Mage_XmlConnect_Model_Application::APP_CODE_COOKIE_NAME;
        $appCode = isset($_COOKIE[$cookieName]) ? (string) $_COOKIE[$cookieName] : '';
        $screenSizeCookieName = Mage_XmlConnect_Model_Application::APP_SCREEN_SIZE_NAME;
        $screenSize = isset($_COOKIE[$screenSizeCookieName]) ? (string) $_COOKIE[$screenSizeCookieName] : '';
        if (!$appCode) {
            $this->_message(Mage::helper('xmlconnect')->__('Specified invalid app code.'), self::MESSAGE_STATUS_ERROR);
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return;
        }
        $appModel = Mage::getModel('xmlconnect/application')->loadByCode($appCode);
        $appModel->setScreenSize($screenSize);
        if ($appModel && $appModel->getId()) {
            Mage::app()->setCurrentStore(Mage::app()->getStore($appModel->getStoreId())->getCode());
            Mage::register('current_app', $appModel);
        }
        else {
            $this->_message(Mage::helper('xmlconnect')->__('Specified invalid app code.'), self::MESSAGE_STATUS_ERROR);
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return;
        }
    }

    /**
     * Validate response body
     */
    public function postDispatch()
    {
        parent::postDispatch();
        $body = $this->getResponse()->getBody();
        if (empty($body)) {
            $this->_message(
                Mage::helper('xmlconnect')->__('An error occurred while processing your request.'),
                self::MESSAGE_STATUS_ERROR
            );
        }
    }

    /**
     * Generate message xml and set it to response body
     * @param string $text
     * @param string $status
     */
    protected function _message($text, $status, $type='', $action='')
    {
        $message = new Mage_XmlConnect_Model_Simplexml_Element('<message></message>');
        $message->addChild('status', $status);
        $message->addChild('text', $text);
        $this->getResponse()->setBody($message->asNiceXml());
    }
}
