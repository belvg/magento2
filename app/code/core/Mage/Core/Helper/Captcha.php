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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Captcha image model
 *
 * @category   Mage
 * @package    Mage_Core
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Helper_Captcha extends Mage_Core_Helper_Abstract
{
    const SESSION_FAILED_ATTEMPT_PREFIX = 'failed_attempt_';
    // Number of unsuccessful attempts captcha will be shown after
    const XML_PATH_FRONTEND_CAPTCHA_FAILED_ATTEMPTS = 'customer/captcha/failed_attempts';
    const XML_PATH_BACKEND_CAPTCHA_FAILED_ATTEMPTS  = 'default/admin/captcha/failed_attempts';
    // See MODE_* constants
    const XML_PATH_FRONTEND_CAPTCHA_MODE            = 'customer/captcha/mode';
    const XML_PATH_BACKEND_CAPTCHA_MODE             = 'default/admin/captcha/mode';
    // Captcha enabled or disabled
    const XML_PATH_FRONTEND_CAPTCHA_ENABLE          = 'customer/captcha/enable';
    const XML_PATH_BACKEND_CAPTCHA_ENABLE           = 'default/admin/captcha/enable';
    // After this number of seconds captcha won't be correct even if the word was guessed correctly
    const XML_PATH_FRONTEND_CAPTCHA_TIMEOUT         = 'customer/captcha/timeout';
    const XML_PATH_BACKEND_CAPTCHA_TIMEOUT          = 'default/admin/captcha/timeout';
    // Font to render captcha
    const XML_PATH_FRONTEND_CAPTCHA_FONT            = 'customer/captcha/font';
    const XML_PATH_BACKEND_CAPTCHA_FONT             = 'default/admin/captcha/font';
    // Number of symbols in captcha
    const XML_PATH_FRONTEND_CAPTCHA_WORD_LENGTH     = 'customer/captcha/length';
    const XML_PATH_BACKEND_CAPTCHA_WORD_LENGTH      = 'default/admin/captcha/length';
    // Symbols used to generate captcha
    const XML_PATH_FRONTEND_CAPTCHA_SYMBOLS         = 'customer/captcha/symbols';
    const XML_PATH_BACKEND_CAPTCHA_SYMBOLS          = 'default/admin/captcha/symbols';
    // Whether to respect case while checking the answer
    const XML_PATH_FRONTEND_CAPTCHA_CASE_SENSITIVE   = 'customer/captcha/case_sensitive';
    const XML_PATH_BACKEND_CAPTCHA_CASE_SENSITIVE    = 'default/admin/captcha/case_sensitive';
    // List of available fonts
    const XML_PATH_CAPTCHA_FONTS                    = 'default/captcha/fonts';
    // List of form IDs where captcha is always enabled
    const XML_PATH_CAPTCHA_ALWAYS_FOR               = 'default/captcha/always_for';
    // Always show captcha
    const MODE_ALWAYS     = 'always';
    // Show captcha only after certain number of unsuccessful attempts
    const MODE_AFTER_FAIL = 'after_fail';

    // Used for "name" attribute of captcha's input field
    const INPUT_NAME_FIELD_VALUE = 'captcha';

    /* @var Mage_Core_Model_Session */
    protected $_session = null;

    /**
     * Returns session ID for particular form ID
     *
     * @param string $formId
     * @return string
     */
    protected function _getFailedAttemptsSessionId($formId)
    {
        return self::SESSION_FAILED_ATTEMPT_PREFIX . $formId;
    }

    /**
     * Executed in case unsuccessful attempt was made (incorrect login/password on login page, for example)
     *
     * @param string $formId
     * @return int
     */
    protected function _logFailedAttempt($formId)
    {
        $sessionId = $this->_getFailedAttemptsSessionId($formId);
        $attemptCnt = (int)$this->_getSession()->getData($sessionId);
        $attemptCnt++;
        $this->_getSession()->setData($sessionId, $attemptCnt);
        return $attemptCnt;
    }

    /**
     * Resets counter for previously logged incorrect attempts
     *
     * @param string $formId
     * @return Mage_Core_Helper_Captcha
     */
    protected function _resetFailedAttempts($formId)
    {
        $this->_getSession()->unsetData($this->_getFailedAttemptsSessionId($formId));
        return $this;
    }

    /**
     * Returns number of unsuccessful attempts after which captcha is shown
     *
     * @return int
     */
    protected function _getShowAfterFailedAttemptsNum()
    {
        $showAfterFailedAttemptsNum = (int)$this->getConfigNode('failed_attempts');
        return $showAfterFailedAttemptsNum;
    }

    /**
     * Returns session where to save data between page refreshes
     *
     * @return Mage_Core_Model_Session
     */
    protected function _getSession()
    {
        if (!$this->_session) {
            $this->_session = Mage::getSingleton('core/session');
        }
        return $this->_session;
    }

    /**
     * Whether to show captcha for this form every time
     *
     * @param string $formId
     * @return bool
     */
    protected function _isShowAlways($formId = '')
    {
        $node = $this->getConfigNode('mode');
        $isShowAlways = ((string)$node == self::MODE_ALWAYS);
        if (!$isShowAlways && $formId) {
            // Check, maybe we need to always show a captcha for this particular form
            $node = Mage::getConfig()->getNode(self::XML_PATH_CAPTCHA_ALWAYS_FOR);
            if ($node) {
                foreach ($node->children() as $nodeFormId => $isAlwaysFor) {
                    if ((bool)(string)$isAlwaysFor && ($formId == $nodeFormId)) {
                        $isShowAlways = true;
                        break;
                    }
                }
            }
        }
        return $isShowAlways;
    }

    /**
     * Whether captcha is enabled at this area
     *
     * @return bool
     */
    protected function _isEnabled()
    {
        $isEnabled = (bool)(string)$this->getConfigNode('enable');
        return $isEnabled;
    }

    /**
     * Returns value of the node with respect to current area (frontend or backend)
     *
     * @param string $id The last part of XML_PATH_$area_CAPTCHA_ constant (case insensitive)
     * @throws Mage_Core_Exception
     * @return Mage_Core_Model_Config_Element
     */
    public function getConfigNode($id)
    {
        $id = strtoupper($id);
        /** @var $currentStore Mage_Core_Model_Store */
        $currentStore = Mage::app()->getStore();
        $isAdmin = $currentStore->isAdmin();
        $area = $isAdmin ? 'BACKEND' : 'FRONTEND';
        $constName = "XML_PATH_{$area}_CAPTCHA_{$id}";
        if (!defined("self::{$constName}")) {
            $class = get_class($this);
            Mage::throwException("{$class}::{$constName} is undefined");
        }

        $path = constant("self::{$constName}");
        if ($isAdmin) {
            $node = Mage::getConfig()->getNode($path);
        } else {
            // For frontend area all config fields have WEBSITE scope
            $node = Mage::getConfig()->getNode($path, 'websites', (int) $currentStore->getWebsiteId());
        }

        return $node;
    }

    /**
     * Checks whether an attempt was successful or not and does appropriate actions
     *
     * @param bool   $isSuccess
     * @param string $formId
     * @return void
     */
    public function checkAttempt($isSuccess, $formId)
    {
        if ($isSuccess) {
            $this->_resetFailedAttempts($formId);
        } else {
            $this->_logFailedAttempt($formId);
        }
    }

    /**
     * Whether captcha is required to be inserted to this form
     *
     * @param string $formId
     * @return bool
     */
    public function isRequired($formId)
    {
        if (empty($formId) || !$this->_isEnabled()) {
            return false;
        }
        if ($this->_isShowAlways($formId)) {
            return true;
        }
        $sessionId = $this->_getFailedAttemptsSessionId($formId);
        $loggedFailedAttempts = (int)$this->_getSession()->getData($sessionId);
        $showAfterFailedAttempts = $this->_getShowAfterFailedAttemptsNum();
        $isRequired = ($loggedFailedAttempts >= $showAfterFailedAttempts);
        return $isRequired;
    }

    /**
     * Get list of available fonts
     * Return format:
     * [['arial'] => ['label' => 'Arial', 'path' => '/www/magento/fonts/arial.ttf']]
     *
     * @return array
     */
    public function getFonts()
    {
        $node = Mage::getConfig()->getNode(self::XML_PATH_CAPTCHA_FONTS);
        $fonts = array();
        if ($node) {
            foreach ($node->children() as $fontName => $fontNode) {
                if (!empty($fontNode->label) && !empty($fontNode->path)) {
                    $path = (string)$fontNode->path;
                    if (!realpath($path)) {
                        // Seems it is not full path - adding base dir
                        $path = realpath(Mage::getBaseDir() . DS . $path);
                    }
                    if ($path && file_exists($path) && (is_file($path) || is_link($path))) {
                        $fonts[$fontName] = array('label' => (string)$fontNode->label, 'path' => $path);
                    }
                }
            }
        }
        return $fonts;
    }
}
