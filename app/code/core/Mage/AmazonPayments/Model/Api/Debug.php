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
 * @package     Mage_AmazonPayments
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * AmazonPayments Api Debug Model
 *
 * @method Mage_AmazonPayments_Model_Resource_Api_Debug _getResource()
 * @method Mage_AmazonPayments_Model_Resource_Api_Debug getResource()
 * @method Mage_AmazonPayments_Model_Api_Debug getTransactionId()
 * @method string setTransactionId(string $value)
 * @method Mage_AmazonPayments_Model_Api_Debug getDebugAt()
 * @method string setDebugAt(string $value)
 * @method Mage_AmazonPayments_Model_Api_Debug getRequestBody()
 * @method string setRequestBody(string $value)
 * @method Mage_AmazonPayments_Model_Api_Debug getResponseBody()
 * @method string setResponseBody(string $value)
 *
 * @category    Mage
 * @package     Mage_AmazonPayments
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_AmazonPayments_Model_Api_Debug extends Mage_Core_Model_Abstract
{
    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('amazonpayments/api_debug');
    }
}
