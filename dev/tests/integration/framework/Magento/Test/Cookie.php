<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Replacement for the native cookie model that doesn't send cookie headers in testing environment
 */
class Magento_Test_Cookie extends Mage_Core_Model_Cookie
{
    /**
     * Request instance
     *
     * @var Mage_Core_Controller_Request_Http
     */
    private $_request;

    /**
     * Response instance
     *
     * @var Mage_Core_Controller_Response_Http
     */
    private $_response;

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Controller_Response_Http $response
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request = null, Mage_Core_Controller_Response_Http $response = null
    ) {
        $this->_request = $request;
        $this->_response = $response;
    }

    /**
     * Retrieve a request instance suitable for the testing environment
     *
     * @return Mage_Core_Controller_Request_Http
     */
    protected function _getRequest()
    {
        if ($this->_request) {
            return $this->_request;
        }
        return parent::_getRequest();
    }

    /**
     * Retrieve a request instance suitable for the testing environment
     *
     * @return Mage_Core_Controller_Response_Http
     */
    protected function _getResponse()
    {
        if ($this->_response) {
            return $this->_response;
        }
        return parent::_getResponse();
    }

    /**
     * Dummy function, which sets value directly to $_COOKIE super-global array instead of calling setcookie()
     *
     * @param string $name The cookie name
     * @param string $value The cookie value
     * @param int $period Lifetime period
     * @param string $path
     * @param string $domain
     * @param int|bool $secure
     * @param bool $httponly
     * @return Magento_Test_Cookie
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function set($name, $value, $period = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        $_COOKIE[$name] = $value;
        return $this;
    }

    /**
     * Dummy function, which removes value directly from $_COOKIE super-global array instead of calling setcookie()
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     * @param int|bool $secure
     * @param int|bool $httponly
     * @return Magento_Test_Cookie
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete($name, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        unset($_COOKIE[$name]);
        return $this;
    }
}
