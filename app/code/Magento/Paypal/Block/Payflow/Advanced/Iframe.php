<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Block\Payflow\Advanced;

/**
 * Payflow Advanced iframe block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Iframe extends \Magento\Paypal\Block\Payflow\Link\Iframe
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Paypal\Helper\Hss $hssHelper
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Paypal\Helper\Hss $hssHelper,
        \Magento\Payment\Helper\Data $paymentData,
        array $data = []
    ) {
        parent::__construct($context, $orderFactory, $checkoutSession, $hssHelper, $paymentData, $data);
        $this->_isScopePrivate = false;
    }

    /**
     * Set payment method code
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_paymentMethodCode = \Magento\Paypal\Model\Config::METHOD_PAYFLOWADVANCED;
    }

    /**
     * Get frame action URL
     *
     * @return string
     */
    public function getFrameActionUrl()
    {
        return $this->getUrl('paypal/payflowadvanced/form', ['_secure' => true]);
    }

    /**
     * Check sandbox mode
     *
     * @return bool
     */
    public function isTestMode()
    {
        $mode = $this->_paymentData->getMethodInstance(
            \Magento\Paypal\Model\Config::METHOD_PAYFLOWADVANCED
        )->getConfigData(
            'sandbox_flag'
        );
        return (bool)$mode;
    }
}
