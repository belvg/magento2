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
 * @package     Mage_OAuth
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Application model
 *
 * @category    Mage
 * @package     Mage_OAuth
 * @author      Magento Core Team <core@magentocommerce.com>
 * @method Mage_OAuth_Model_Resource_Consumer _getResource()
 * @method Mage_OAuth_Model_Resource_Consumer getResource()
 * @method Mage_OAuth_Model_Resource_Consumer_Collection getCollection()
 * @method Mage_OAuth_Model_Resource_Consumer_Collection getResourceCollection()
 * @method string getName()
 * @method Mage_OAuth_Model_Consumer setName() setName(string $name)
 * @method string getKey()
 * @method Mage_OAuth_Model_Consumer setKey() setKey(string $key)
 * @method string getSecret()
 * @method Mage_OAuth_Model_Consumer setSecret() setSecret(string $secret)
 * @method string getCallbackUrl()
 * @method Mage_OAuth_Model_Consumer setCallbackUrl() setCallbackUrl(string $url)
 * @method string getCreatedAt()
 * @method Mage_OAuth_Model_Consumer setCreatedAt() setCreatedAt(string $date)
 * @method string getUpdatedAt()
 * @method Mage_OAuth_Model_Consumer setUpdatedAt() setUpdatedAt(string $date)
 */
class Mage_OAuth_Model_Consumer extends Mage_Core_Model_Abstract
{
    /**
     * Key hash length
     */
    const KEY_LENGTH = 32;

    /**
     * Secret hash length
     */
    const SECRET_LENGTH = 32;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('oauth/consumer');
    }

    /**
     * Update "updated at" date
     *
     * @return Mage_OAuth_Model_Consumer
     */
    protected function _beforeSave()
    {
        if (!$this->getId()) {
            $this->setUpdatedAt(time());
        }
        $this->validate();
        parent::_beforeSave();
        return $this;
    }

    /**
     * Validate data
     *
     * @return array|bool
     * @throw Mage_Core_Exception|Exception   Throw exception on fail validation
     */
    public function validate()
    {
        if ($this->getCallBackUrl()) {
            /** @var $validatorUrl Mage_OAuth_Model_Consumer_Validator_CallbackUrl */
            $validatorUrl = Mage::getSingleton('oauth/consumer_validator_callbackUrl');
            if (!$validatorUrl->isValid($this->getCallBackUrl())) {
                Mage::throwException(array_shift($validatorUrl->getMessages()));
            }
        }

        /** @var $validatorLength Mage_OAuth_Model_Consumer_Validator_KeyLength */
        $validatorLength = Mage::getModel(
            'oauth/consumer_validator_keyLength',
            array('length' => self::KEY_LENGTH));

        $validatorLength->setName('Consumer Key');
        if (!$validatorLength->isValid($this->getKey())) {
            Mage::throwException(array_shift($validatorLength->getMessages()));
        }

        $validatorLength->setLength(self::SECRET_LENGTH);
        $validatorLength->setName('Consumer Secret');
        if (!$validatorLength->isValid($this->getSecret())) {
            Mage::throwException(array_shift($validatorLength->getMessages()));
        }
        return true;
    }
}
