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
 * @category   Enterprise
 * @package    Enterprise_Invitation
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Invitation adminhtml controller
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */

class Enterprise_Invitation_Adminhtml_InvitationController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Invitation list
     *
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('customer/invitation');
        $this->renderLayout();
    }

    /**
     * Init invitation model by request
     *
     * @return Enterprise_Invitation_Model_Invitation
     */
    protected function _initInvitation()
    {
        $invitationId = $this->getRequest()->getParam('id');
        $invitation = Mage::getModel('invitation/invitation')
            ->load($invitationId);

        if (!$invitation->getId()) {
            $this->_getSession()->addError(
                Mage::helper('invitation')->__('Invitaion not found')
            );
            $this->_redirect('*/*/');
            return false;
        }

        return $invitation;
    }

    /**
     * Invitaion view action
     *
     * @return void
     */
    public function viewAction()
    {
        $invitation = $this->_initInvitation();
        if (!$invitation) {
            return;
        }

        Mage::register('current_invitation', $invitation);

        $this->loadLayout()
            ->_setActiveMenu('customer/invitation');
        $this->renderLayout();
    }

    /**
     * Create new invitatoin form
     *
     * @return void
     */
    public function newAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('invitation');
        $this->renderLayout();
    }

    /**
     * Create new invitations
     *
     * @return void
     */
    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {
            $emails = preg_split('/[\\s,;]/', $this->getRequest()->getParam('email'));
            $this->_getSession()->setInvitationFormData($this->getRequest()->getPost());
            if (Mage::app()->isSingleStoreMode()) {
                $storeId = Mage::app()->getStore(true)->getId();
            } else {
                $storeId = $this->getRequest()->getParam('store_id');
            }

            $groupId = $this->getRequest()->getParam('group_id');

            if (empty($storeId)) {
                $this->_getSession()->addError(
                    Mage::helper('invitation')->__('Please select store')
                );
                $this->_redirect('*/*/new');
                return;
            }

            if (empty($groupId)) {
                $this->_getSession()->addError(
                    Mage::helper('invitation')->__('Please select customer group')
                );
                $this->_redirect('*/*/new');
                return;
            }

            $now = Mage::app()->getLocale()->date()
                    ->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE)
                    ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

            foreach ($emails as $email) {
                $email = trim($email);
                if (!empty($email)) {
                    if (Mage::getModel('customer/customer')->setWebsiteId(
                            Mage::app()->getStore($storeId)->getWebsiteId()
                        )->loadByEmail($email)->getId()) {
                        continue;
                    }
                    try {
                        $invitation = Mage::getModel('invitation/invitation');
                        $invitation->setGroupId($groupId)
                            ->setEmail($email)
                            ->setProtectionCode($invitation->generateCode())
                            ->setDate($now)
                            ->setStoreId($storeId)
                            ->setMessage($this->getRequest()->getParam('message'))
                            ->setStatus(Enterprise_Invitation_Model_Invitation::STATUS_SENT)
                            ->save();
                        $this->_sendInvitationEmail($invitation);
                        $this->_getSession()->addSuccess(
                            Mage::helper('invitation')->__('Invitation for %s has been sent successfully.', $email)
                        );
                    } catch (Mage_Core_Exception $e) {
                        $this->_getSession()->addError(
                            Mage::helper('invitation')->__(
                                'Email to %s was not sent becouse "%s". Please try again later.',
                                $email,
                                $e->getMessage()
                            )
                        );
                    } catch (Exception $e) {
                        $this->_getSession()->addError(
                            Mage::helper('invitation')->__('Email to %s was not sent for some reason. Please try again later.', $email)
                        );
                    }
                }
            }

            $this->_getSession()->unsInvitationFormData();
        }

        $this->_redirect('*/*/');
    }

    public function saveMessageAction()
    {
        if ($this->getRequest()->isPost()) {
            $invitation = $this->_initInvitation();
            if (!$invitation) {
                return;
            }

            if ($invitation->getStatus() == Enterprise_Invitation_Model_Invitation::STATUS_SENT) {
                try {
                    $invitation->setMessage($this->getRequest()->getParam('message'))
                        ->save();
                    $this->_getSession()->addSuccess(
                        Mage::helper('invitation')->__('Invitation message was successfully saved.')
                    );
                } catch (Mage_Core_Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                } catch (Exception $e) {
                    $this->_getSession()->addError(
                        Mage::helper('invitation')->__('Invitation message was not sent for some reason. Please try again later.')
                    );
                }
            } else  {
                $this->_getSession()->addError(
                    Mage::helper('invitation')->__('You cannot edit message for this invitation.')
                );
            }
        } else {

        }

        $this->_redirect('*/*/view', array('_current'=>true));
    }

    /**
     * Invitation cancel action
     *
     * @return void
     */
    public function cancelAction()
    {
        $invitation = $this->_initInvitation();
        if (!$invitation) {
            return;
        }

        if ($invitation->getStatus() !== Enterprise_Invitation_Model_Invitation::STATUS_SENT) {
            $this->_getSession()->addError(
                Mage::helper('invitation')->__('You cannot cancel this invitation')
            );
            $this->_redirect('*/*/view', array('_current'=>true));
            return;
        }

        try {
            $invitation->setStatus(Enterprise_Invitation_Model_Invitation::STATUS_CANCELED)
                ->save();
            $this->_getSession()->addSuccess(
                Mage::helper('invitation')->__('Invitation was successfully canceled.')
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('invitation')->__('Invitation was not canceled. Try again later.')
            );
        }

        $this->_redirect('*/*/view', array('_current'=>true));

    }

    /**
     * Invitation re-send action
     *
     * @return void
     */
    public function resendAction()
    {
        $invitation = $this->_initInvitation();
        if (!$invitation) {
            return;
        }

        if ($invitation->getStatus() !== Enterprise_Invitation_Model_Invitation::STATUS_SENT) {
            $this->_getSession()->addError(
                Mage::helper('invitation')->__('You cannot re-send this invitation')
            );
            $this->_redirect('*/*/view', array('_current'=>true));
            return;
        }

        try {
            $this->_sendInvitationEmail($invitation);
            $invitation->setOrigData('status', '')
                ->save();
            $this->_getSession()->addSuccess(
                Mage::helper('invitation')->__('Invitation was successfully re-sent.')
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('invitation')->__('Invitation was not re-sent. Try again later.')
            );
        }

        $this->_redirect('*/*/view', array('_current'=>true));

    }

    /**
     * Massaction resend
     *
     * @return void
     */
    public function massResendAction()
    {
        $invitations = $this->getRequest()->getParam('invitations', array());
        if (empty($invitations) || !is_array($invitations)) {
            $this->_getSession()->addError(
                Mage::helper('invitation')->__('Please select invitations.')
            );
            $this->_redirect('*/*/');
            return;
        }

        $collection = Mage::getModel('invitation/invitation')->getCollection();
        $collection->addFieldToFilter('invitation_id', array('in'=>$invitations))
            ->addFieldToFilter('status', Enterprise_Invitation_Model_Invitation::STATUS_SENT);

        $now = Mage::app()->getLocale()->date()
            ->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE)
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $amount = 0;
        foreach ($collection as $invitation) {
            try {
                $this->_sendInvitationEmail($invitation);
                $invitation->setOrigData('status', '');
                $invitation->save();
                $amount ++;
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('invitation')->__(
                        'Email to %s was not sent for some reason. Please try again later.',
                        $invitation->getEmail()
                    )
                );
            }
        }

        if ($amount > 0) {
             $this->_getSession()->addSuccess(
                Mage::helper('invitation')->__('%d invitation(s) was re-sent.', $amount)
             );
        } else {
            $this->_getSession()->addWarning(
                Mage::helper('invitation')->__('No invitations was re-sent.')
             );
        }

        $this->_redirect('*/*/');
    }

    /**
     * Massaction cancel
     *
     * @return void
     */
    public function massCancelAction()
    {
        $invitations = $this->getRequest()->getParam('invitations', array());
        if (empty($invitations) || !is_array($invitations)) {
            $this->_getSession()->addError(
                Mage::helper('invitation')->__('Please select invitations.')
            );
            $this->_redirect('*/*/');
            return;
        }

        $collection = Mage::getModel('invitation/invitation')->getCollection();
        $collection->addFieldToFilter('invitation_id', array('in'=>$invitations))
            ->addFieldToFilter('status', Enterprise_Invitation_Model_Invitation::STATUS_SENT);

        $amount = 0;
        foreach ($collection as $invitation) {
            try {
                $invitation->setStatus(Enterprise_Invitation_Model_Invitation::STATUS_CANCELED)
                    ->save();
                $amount ++;
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('invitation')->__(
                        'Inventation for %s was not canceled for some reason. Please try again later.',
                        $invitation->getEmail()
                    )
                );
            }
        }

        if ($amount > 0) {
             $this->_getSession()->addSuccess(
                Mage::helper('invitation')->__('%d invitation(s) was canceled.', $amount)
             );
        } else {
            $this->_getSession()->addWarning(
                Mage::helper('invitation')->__('No invitations was canceled.')
             );
        }

        $this->_redirect('*/*/');
    }

    /**
     * Acl admin user check
     *
     * @return boolean
     */
    protected function isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/invitation');
    }

    /**
     * Send inventation email
     *
     * @param Portero_Invitation_Model_Invitation $invitation
     * @return Portero_Invitation_Adminhtml_InvitationController
     */
    protected function _sendInvitationEmail($invitation)
    {

        $template = Mage::getStoreConfig('invitation/email/template', $invitation->getStoreId());
        $sender = Mage::getStoreConfig('invitation/email/identity', $invitation->getStoreId());
        $mail = Mage::getModel('core/email_template');
        $mail->setDesignConfig(array('area'=>'frontend', 'store'=>$invitation->getStoreId()))
             ->sendTransactional(
                $template,
                $sender,
                $invitation->getEmail(),
                null,
                array(
                    'url' => Mage::helper('invitation')->getInvitationUrl($invitation),
                    'allow_message' => Mage::getStoreConfigFlag('invitation/general/allow_customer_message', $invitation->getStoreId()),
                    'message' => htmlspecialchars($invitation->getMessage())
                )
            );

        return $this;
    }
}