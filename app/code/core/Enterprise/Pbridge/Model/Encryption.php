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
 * @package     Enterprise_Pbridge
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

class Enterprise_Pbridge_Model_Encryption extends Enterprise_Pci_Model_Encryption {

    /**
     * Constructor
     */
    public function __construct()
    {
        // load a secure key from config
        $this->_keys = array(trim((string)$pbridgeUrl = Mage::getStoreConfig('payment/pbridge/transferkey')));
        $this->_keyVersion = 0;
    }
    
    /**
     * Look for key and crypt versions in encrypted data before decrypting
     *
     * @param string $data
     * @return string
     */
    public function decrypt($data)
    {
        return parent::decrypt($this->_keyVersion . ':' . self::CIPHER_LATEST . ':' . $data);
    }

    /**
     * Prepend IV to encrypted data after encrypting
     *
     * @param string $data
     * @return string
     */
    public function encrypt($data)
    {
        $crypt = $this->_getCrypt();
        return $crypt->getInitVector() . ':' . base64_encode($crypt->encrypt((string)$data));
    }
}

