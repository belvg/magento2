<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Data converter
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Payment_Method_Converter
{
    /**
     * List of fields that has to be encrypted
     * Format: method_name => array(field1, field2, ... )
     *
     * @var array
     */
    protected $_encryptFields = array(
        'ccsave' => array(
            'cc_owner' => true,
            'cc_exp_year' => true,
            'cc_exp_month' => true,
        ),
    );

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_encryptor;

    public function __construct(array $data = array())
    {
        $this->_encryptor = isset($data['encryptor']) ? $data['encryptor'] : Mage::helper('Mage_Core_Helper_Data');
    }

    /**
     * Check if specified field is encrypted
     *
     * @param Mage_Core_Model_Abstract $object
     * @param string $filedName
     * @return bool
     */
    protected function _shouldBeEncrypted(Mage_Core_Model_Abstract $object, $filedName)
    {
        $method = $object->getData('method');
        return isset($this->_encryptFields[$method][$filedName]) &&
            $this->_encryptFields[$method][$filedName];
    }


    /**
     * Decode data
     *
     * @param Mage_Core_Model_Abstract $object
     * @param string $filedName
     * @return mixed
     */
    public function decode(Mage_Core_Model_Abstract $object, $filedName)
    {
        $value = $object->getData($filedName);

        if ($this->_shouldBeEncrypted($object, $filedName)) {
            $value = $this->_encryptor->decrypt($value);
        }

        return $value;
    }

    /**
     * Encode data
     *
     * @param Mage_Core_Model_Abstract $object
     * @param string $filedName
     * @return mixed
     */
    public function encode(Mage_Core_Model_Abstract $object, $filedName)
    {
        $value = $object->getData($filedName);

        if ($this->_shouldBeEncrypted($object, $filedName)) {
            $value = $this->_encryptor->encrypt($value);
        }

        return $value;
    }
}