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
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * PayPal Standard payment "form"
 */
class Mage_Paypal_Block_Express_Form extends Mage_Paypal_Block_Standard_Form
{
    /**
     * Payment method code
     * @var string
     */
    protected $_methodCode = Mage_Paypal_Model_Config::METHOD_WPP_EXPRESS;

    /**
     * Set template and redirect message
     */
    protected function _construct()
    {
        $result = parent::_construct();
        $this->setRedirectMessage(Mage::helper('paypal')->__('You will be redirected to PayPal website.'));
        return $result;
    }
}
