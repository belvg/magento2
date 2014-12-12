<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Model\Billing;

/**
 * Billing Agreement abstaract class
 */
abstract class AbstractAgreement extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Payment method instance
     *
     * @var \Magento\Payment\Model\MethodInterface
     */
    protected $_paymentMethodInstance = null;

    /**
     * Billing Agreement Errors
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Init billing agreement
     *
     * @return string
     */
    abstract public function initToken();

    /**
     * Verify billing agreement details
     *
     * @return $this
     */
    abstract public function verifyToken();

    /**
     * Create billing agreement
     *
     * @return $this
     */
    abstract public function place();

    /**
     * Cancel billing agreement
     *
     * @return $this
     */
    abstract public function cancel();

    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData = null;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_paymentData = $paymentData;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve payment method instance
     *
     * @return \Magento\Payment\Model\MethodInterface
     */
    public function getPaymentMethodInstance()
    {
        if (is_null($this->_paymentMethodInstance)) {
            $this->_paymentMethodInstance = $this->_paymentData->getMethodInstance($this->getMethodCode());
            $this->_paymentMethodInstance->setStore($this->getStoreId());
        }
        return $this->_paymentMethodInstance;
    }

    /**
     * Validate data before save
     *
     * @return bool
     */
    public function isValid()
    {
        $this->_errors = [];
        if (is_null($this->getPaymentMethodInstance()) || !$this->getPaymentMethodInstance()->getCode()) {
            $this->_errors[] = __('The payment method code is not set.');
        }
        if (!$this->getReferenceId()) {
            $this->_errors[] = __('The reference ID is not set.');
        }
        return empty($this->_errors);
    }

    /**
     * Before save, it's overridden just to make data validation on before save event
     *
     * @throws \Magento\Framework\Model\Exception
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function beforeSave()
    {
        if ($this->isValid()) {
            return parent::beforeSave();
        }
        array_unshift($this->_errors, __('Unable to save Billing Agreement:'));
        throw new \Magento\Framework\Model\Exception(implode(' ', $this->_errors));
    }
}