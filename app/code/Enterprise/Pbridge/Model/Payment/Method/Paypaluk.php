<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Paypal UK Direct dummy payment method model
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Pbridge_Model_Payment_Method_Paypaluk extends Mage_PaypalUk_Model_Direct
{
    /**
     * Form block type for the frontend
     *
     * @var string
     */
    protected $_formBlockType = 'Enterprise_Pbridge_Block_Checkout_Payment_Paypaluk';

    /**
     * Form block type for the backend
     *
     * @var string
     */
    protected $_backendFormBlockType = 'Enterprise_Pbridge_Block_Adminhtml_Sales_Order_Create_Paypal';

    /**
     * Payment Bridge Payment Method Instance
     *
     * @var Enterprise_Pbridge_Model_Payment_Method_Pbridge
     */
    protected $_pbridgeMethodInstance = null;

    /**
     * Website Payments Pro instance type
     *
     * @var $_proType string
     */
    protected $_proType = 'Enterprise_Pbridge_Model_Payment_Method_Paypaluk_Pro';

    /**
     * Constructor
     *
     * @param array $params
     */
    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->_pro->setPaymentMethod($this);
    }

    /**
     * Return that current payment method is dummy
     *
     * @return boolean
     */
    public function getIsDummy()
    {
        return true;
    }

    /**
     * Return Payment Bridge method instance
     *
     * @return Enterprise_Pbridge_Model_Payment_Method_Pbridge
     */
    public function getPbridgeMethodInstance()
    {
        if ($this->_pbridgeMethodInstance === null) {
            $this->_pbridgeMethodInstance = Mage::helper('Mage_Payment_Helper_Data')->getMethodInstance('pbridge');
            $this->_pbridgeMethodInstance->setOriginalMethodInstance($this);
        }
        return $this->_pbridgeMethodInstance;
    }

    /**
     * Retrieve dummy payment method code
     *
     * @return string
     */
    public function getCode()
    {
        return 'pbridge_' . parent::getCode();
    }

    /**
     * Retrieve original payment method code
     *
     * @return string
     */
    public function getOriginalCode()
    {
        return parent::getCode();
    }

    /**
     * Retrieve payment method title
     *
     * @return string
     */
    public function getTitle()
    {
        return parent::getTitle();
    }

    /**
     * Assign data to info model instance
     *
     * @param  mixed $data
     * @return Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        $this->getPbridgeMethodInstance()->assignData($data);
        return $this;
    }

    /**
     * Retrieve information from original payment configuration
     *
     * @param   string $field
     * @return  mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $path = 'payment/'.$this->getOriginalCode().'/'.$field;
        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * Check whether payment method can be used
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return boolean
     */
    public function isAvailable($quote = null)
    {
        return $this->getPbridgeMethodInstance()->isDummyMethodAvailable($quote);
    }

    /**
     * Retrieve block type for method form generation
     *
     * @return string
     */
    public function getFormBlockType()
    {
        return Mage::app()->getStore()->isAdmin() ?
            $this->_backendFormBlockType :
            $this->_formBlockType;
    }

    /**
     * Prepare info instance for save
     *
     * @return Enterprise_Pbridge_Model_Payment_Method_Paypal
     */
    public function prepareSave()
    {
        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return Enterprise_Pbridge_Model_Payment_Method_Paypal
     */
    public function validate()
    {
        $this->getPbridgeMethodInstance()->validate();
        return $this;
    }

    /**
     * Authorize payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Direct
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $result = new Varien_Object($this->getPbridgeMethodInstance()->authorize($payment, $amount));
        $order = $payment->getOrder();
        $result->setEmail($order->getCustomerEmail());
        $this->_importResultToPayment($result, $payment);
        return $this;
    }

    /**
     * Capture payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Direct
     */
    public function capture(Varien_Object $payment, $amount)
    {
        if (false === $this->_pro->capture($payment, $amount)) {
            $this->authorize($payment, $amount);
        }
        return $this;
    }

    /**
     * Refund capture
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Direct
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $this->_pro->refund($payment, $amount);
        return $this;
    }

    /**
     * Void payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Direct
     */
    public function void(Varien_Object $payment)
    {
        $this->_pro->void($payment);
        return $this;
    }

    /**
     * Import direct payment results to payment
     *
     * @param Varien_Object $api
     * @param Mage_Sales_Model_Order_Payment $payment
     */
    protected function _importResultToPayment($api, $payment)
    {
        $payment->setTransactionId($api->getTransactionId())->setIsTransactionClosed(0)
            ->setIsTransactionPending($api->getIsPaymentPending());
        $payflowTrxid = $api->getData(Enterprise_Pbridge_Model_Payment_Method_Paypaluk_Pro::TRANSPORT_PAYFLOW_TXN_ID);
        $payment->setPreparedMessage(
            Mage::helper('Enterprise_Pbridge_Helper_Data')->__('Payflow PNREF: #%s.', $payflowTrxid)
        );

        $this->_pro->importPaymentInfo($api, $payment);
    }

    /**
     * Disable magento centinel validation for pbridge payment methods
     *
     * @return bool
     */
    public function getIsCentinelValidationEnabled()
    {
        return false;
    }

    /**
     * Store id setter, also set storeId to helper
     *
     * @param int|string|Mage_Code_Model_Store $store
     *
     * @return Enterprise_Pbridge_Model_Payment_Method_Paypaluk
     */
    public function setStore($store)
    {
        $this->setData('store', $store);
        Mage::helper('Enterprise_Pbridge_Helper_Data')->setStoreId(is_object($store) ? $store->getId() : $store);
        parent::setStore($store);

        return $this;
    }
}