<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Reward observer
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Model_Observer
{
    /**
     * Update reward points for customer, send notification
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function saveRewardPoints($observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return;
        }

        $request = $observer->getEvent()->getRequest();
        $customer = $observer->getEvent()->getCustomer();
        if ($data = $request->getPost('reward')) {
            if (!empty($data['points_delta'])) {
                $reward = Mage::getModel('enterprise_reward/reward')
                    ->setData($data)
                    ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_ADMIN)
                    ->setCustomer($customer)
                    ->setActionEntity($customer)
                    ->setRewardUpdateNotification((isset($data['reward_update_notification']) ? true : false))
                    ->setRewardWarningNotification((isset($data['reward_warning_notification']) ? true : false))
                    ->updateRewardPoints();
            }
        }

        return $this;
    }

    /**
     * Update reward points after customer register
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function customerRegister($observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return $this;
        }
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = $observer->getEvent()->getCustomer();
        if ($customer->isObjectNew()) {
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomer($customer)
                ->setActionEntity($customer)
                ->setStore(Mage::app()->getStore()->getId())
                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_REGISTER)
                ->updateRewardPoints();
        }
        return $this;
    }

    /**
     * Update points balance after review submit
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function reviewSubmit($observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return $this;
        }
        /* @var $review Mage_Review_Model_Review */
        $review = $observer->getEvent()->getObject();
        if ($review->isApproved() && $review->getCustomerId()) {
            /* @var $reward Enterprise_Reward_Model_Reward */
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomerId($review->getCustomerId())
                ->setStore($review->getStoreId())
                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_REVIEW)
                ->setActionEntity($review)
                ->updateRewardPoints();
        }
        return $this;
    }

    /**
     * Update points balance after tag submit
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function tagSubmit($observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return $this;
        }
        /* @var $tag Mage_Tag_Model_Tag */
        $tag = $observer->getEvent()->getObject();
        /**
         * to remove
         */
        $tag->setCustomerId(2);
        if (($tag->getApprovedStatus() == $tag->getStatus()) && $tag->getCustomerId()) {
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomerId($tag->getCustomerId())
                ->setStore($tag->getStoreId())
                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_TAG)
                ->setActionEntity($tag)
                ->updateRewardPoints();
        }
        return $this;
    }

    /**
     * Update points balance after first successful subscribtion
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function customerSubscribed($observer)
    {
        /* @var $subscriber Mage_Newsletter_Model_Subscriber */
        $subscriber = $observer->getEvent()->getSubscriber();
        // reward only new subscribtions
        if (!$subscriber->isObjectNew() || !$subscriber->getCustomerId()) {
            return $this;
        }

        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return $this;
        }

        $reward = Mage::getModel('enterprise_reward/reward')
            ->setCustomerId($subscriber->getCustomerId())
            ->setStore($subscriber->getStoreId())
            ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_NEWSLETTER)
            ->setActionEntity($subscriber)
            ->updateRewardPoints();

        return $this;
    }

    /**
     * Update points balance after customer registered by invitation
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function invitationToCustomer($observer)
    {
        $invitation = $observer->getEvent()->getInvitation();
        /* @var $invitation Enterprise_Invitation_Model_Invitation */

        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return $this;
        }

        if ($invitation->getCustomerId() && $invitation->getReferralId()) {
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomerId($invitation->getCustomerId())
                ->setStore($invitation->getStoreId())
                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_INVITATION_CUSTOMER)
                ->setActionEntity($invitation)
                ->updateRewardPoints();
        }

        return $this;
    }

    /**
     * Update points balance after order becomes completed
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function orderCompleted($observer)
    {
        $order = $observer->getEvent()->getOrder();
        /* @var $invitation Mage_Sales_Model_Order */
        if ($order->getCustomerIsGuest() || !Mage::helper('enterprise_reward')->isEnabled()) {
            return $this;
        }
        if ($order->getState() != Mage_Sales_Model_Order::STATE_COMPLETE) {
            return $this;
        }

        $reward = Mage::getModel('enterprise_reward/reward')
            ->setCustomerId($order->getCustomerId())
            ->loadByCustomer();
        $rate = $reward->getRateToCurrency();
        if ($rate->getId() && $rate->getCurrencyAmount() > 0) {
            $points = intval($order->getBaseSubtotal() * $rate->getPoints() / $rate->getCurrencyAmount());
            if ($points > 0) {
                $reward->setStore($order->getStoreId())
                    ->setActionEntity($order)
                    ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_ORDER_EXTRA)
                    ->setPointsDelta($points)
                    ->save();
            }
        }

        // Also update inviter balance if possible
        $this->_invitationToOrder($observer);

        return $this;
    }

    /**
     * Update inviter points balance after referral's order completed
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    protected function _invitationToOrder($observer)
    {
        $order = $observer->getEvent()->getOrder();
        /* @var $order Mage_Sales_Model_Order */

        $reward = Mage::getModel('enterprise_reward/reward')
            ->setActionEntity($order)
            ->setCustomerId($order->getCustomerId())
            ->setStore($order->getStoreId())
            ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_INVITATION_ORDER)
            ->updateRewardPoints();

        return $this;
    }

    /**
     * Set flag to recollect reward points totals
     *
     * @param Varien_Event_Observer $observer
     * @@return Enterprise_Reward_Model_Observer
     */
    public function quoteCollectTotalsBefore(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $quote->setRewardPointsTotalCollected(false);
        return $this;
    }

    /**
     * Set use reward points flag to new quote
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function quoteMergeAfter($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $source = $observer->getEvent()->getSource();

        if ($source->getUseRewardPoints()) {
            $quote->setUseRewardPoints($source->getUseRewardPoints());
        }
        return $this;
    }

    /**
     * Set reward points balance to quote if customer want to use it.
     * Set Zero Subtotal Checkout to use if customer points cover grand total
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function paymentDataImport(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return $this;
        }
        $input = $observer->getEvent()->getInput();
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getEvent()->getPayment()->getQuote();
        if (!$quote || !$quote->getCustomerId()) {
            return $this;
        }
        $quote->setUseRewardPoints($input->getUseRewardPoints());
        if ($quote->getUseRewardPoints()) {
            /* @var $reward Enterprise_Reward_Model_Reward */
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomer($quote->getCustomer())
                ->setWebsiteId(Mage::app()->getStore($quote->getStoreId())->getWebsiteId())
                ->loadByCustomer();
            if ($reward->getId()) {
                $quote->setRewardInstance($reward);
                $baseGrandTotal = $quote->getBaseGrandTotal()+$quote->getBaseRewardCurrencyAmount();
                if (!$input->getMethod()) {
                    $input->setMethod('free');
                }
//                if ($reward->isEnoughPointsToCoverAmount($baseGrandTotal) && !$input->getMethod()) {
//                    $input->setMethod('free');
//                }
            }
            else {
                $quote->setUseRewardPoints(false);
            }
        }
        return $this;
    }

    /**
     * Enable Zero Subtotal Checkout payment method
     * if customer has enough points to cover grand total
     *
     * @param Varien_Event_Observer $observer
     */
    public function preparePaymentMethod($observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return $this;
        }
        $quote = $observer->getEvent()->getQuote();
        if (!$quote->getId()) {
            return $this;
        }
        /* @var $reward Enterprise_Reward_Model_Reward */
        $reward = $quote->getRewardInstance();
        if (!$reward || !$reward->getId()) {
            return $this;
        }
        $baseQuoteGrandTotal = $quote->getBaseGrandTotal()+$quote->getBaseRewardCurrencyAmount();
        if ($reward->isEnoughPointsToCoverAmount($baseQuoteGrandTotal)) {
            $paymentCode = $observer->getEvent()->getMethodInstance()->getCode();
            $result = $observer->getEvent()->getResult();
            if ('free' === $paymentCode) {
                $result->isAvailable = true;
            } else {
                $result->isAvailable = false;
            }
        }
        return $this;
    }

    /**
     * Reduce reward points if points was used during checkout
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function processOrderPlace(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return $this;
        }
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();
        if ($order->getBaseRewardCurrencyAmount() > 0) {
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId(Mage::app()->getStore($order->getStoreId())->getWebsiteId())
                ->setPointsDelta(-$order->getRewardPointsBalance())
                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_ORDER)
                ->setActionEntity($order)
                ->updateRewardPoints();
        }
        return $this;
    }

    /**
     * Set forced can creditmemo flag if refunded amount less then invoiced amount of reward points
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function orderLoadAfter(Varien_Event_Observer $observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();
        if ($order->canUnhold()) {
            return $this;
        }
        if ($order->isCanceled() ||
            $order->getState() === Mage_Sales_Model_Order::STATE_CLOSED ) {
            return $this;
        }
        if (($order->getBaseRewardCurrencyAmountInvoiced() - $order->getBaseRewardCurrencyAmountRefunded()) > 0) {
            $order->setForcedCanCreditmemo(true);
        }
        return $this;
    }

    /**
     * Set invoiced reward amount to order
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function invoiceSaveAfter(Varien_Event_Observer $observer)
    {
        /* @var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = $observer->getEvent()->getInvoice();
        if ($invoice->getBaseRewardCurrencyAmount()) {
            $order = $invoice->getOrder();
            $order->setRewardCurrencyAmountInvoiced($order->getRewardCurrencyAmountInvoiced() + $invoice->getRewardCurrencyAmount());
            $order->setBaseRewardCurrencyAmountInvoiced($order->getBaseRewardCurrencyAmountInvoiced() + $invoice->getBaseRewardCurrencyAmount());
        }
        return $this;
    }

    /**
     * Set reward points balance to refund before creditmemo register
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function setRewardPointsBalanceToRefund(Varien_Event_Observer $observer)
    {
        $input = $observer->getEvent()->getRequest()->getParam('creditmemo');
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if (isset($input['refund_reward_points']) && isset($input['refund_reward_points_enable'])) {
            $enable = $input['refund_reward_points_enable'];
            $balance = (int)$input['refund_reward_points'];
            $balance = min($creditmemo->getRewardPointsBalance(), $balance);
            if ($enable && $balance) {
                $creditmemo->setRewardPointsBalanceToRefund($balance);
            }
        }
        return $this;
    }

    /**
     * Clear forced can creditmemo if whole reward amount was refunded
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function creditmemoRefund(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getCreditmemo()->getOrder();
        $refundedAmount = (float)($order->getBaseRewardCurrencyAmountRefunded() + $creditmemo->getBaseRewardCurrencyAmount());
        if ((float)$order->getBaseRewardCurrencyAmountInvoiced() == $refundedAmount) {
            $order->setForcedCanCreditmemo(false);
        }
        return $this;
    }

    /**
     * Set refunded reward amount order and update reward points balance if need
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function creditmemoSaveAfter(Varien_Event_Observer $observer)
    {
        /* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if ($creditmemo->getBaseRewardCurrencyAmount()) {
            $order = $creditmemo->getOrder();
            $order->setRewardPointsBalanceRefunded($order->getRewardPointsBalanceRefunded() + $creditmemo->getRewardPointsBalance());
            $order->setRewardCurrencyAmountRefunded($order->getRewardCurrencyAmountRefunded() + $creditmemo->getRewardCurrencyAmount());
            $order->setBaseRewardCurrencyAmountRefunded($order->getBaseRewardCurrencyAmountRefunded() + $creditmemo->getBaseRewardCurrencyAmount());
            $order->setRewardPointsBalanceToRefund($order->getRewardPointsBalanceToRefund() + $creditmemo->getRewardPointsBalanceToRefund());

            if ((int)$creditmemo->getRewardPointsBalanceToRefund() > 0) {
                $reward = Mage::getModel('enterprise_reward/reward')
                    ->setCustomerId($order->getCustomerId())
                    ->setStore($order->getStoreId())
                    ->setPointsDelta((int)$creditmemo->getRewardPointsBalanceToRefund())
                    ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_CREDITMEMO)
                    ->setActionEntity($order)
                    ->save();
            }
        }
        return $this;
    }

    /**
     * Disable entire RP layout
     *
     * @param Varien_Event_Observer $observer
     */
    public function disableLayout($observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            unset($observer->getUpdates()->enterprise_reward);
        }
    }

    /**
     * Send scheduled low balance warning notifications
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return Enterprise_Reward_Model_Observer
     */
    public function scheduledBalanceExpireNotification($schedule)
    {
        $inDays = (int)Mage::helper('enterprise_reward')->getNotificationConfig('expiry_day_before');
        if (!$inDays) {
            return $this;
        }
        $collection = Mage::getResourceModel('enterprise_reward/reward_history_collection')
            ->loadExpiredSoonPoints($inDays)
//            ->setPageSize(20)
//            ->setCurPage(1)
            ->load();

        foreach ($collection as $item) {
            Mage::getModel('enterprise_reward/reward')->sendBalanceWarningNotification($item);
        }

        return $this;
    }
}
