<?php
/**
 * {license}
 *
 * @category    Mage
 * @package     Mage_Api2
 */

/**
 * API2 Abstarct Validator
 *
 * This is an object to which we encapsulate all business logic of validation and different invariants.
 * But instead of different validators, we group all logic in one class but in different methods.
 *
 * If fails validation, then validation method returns false, and
 * getErrors() will return an array of errors that explain why the
 * validation failed.
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Api2_Model_Resource_Validator
{
    /**
     * Array of validation failure errors.
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Set an array of errors
     *
     * @param array $data
     * @return Mage_Api2_Model_Resource_Validator
     */
    protected function _setErrors(array $data)
    {
        $this->_errors = array_values($data);
        return $this;
    }

    /**
     * Add errors
     *
     * @param array $errors
     * @return Mage_Api2_Model_Resource_Validator
     */
    protected function _addErrors($errors)
    {
        foreach ($errors as $error) {
            $this->_addError($error);
        }
        return $this;
    }

    /**
     * Add error
     *
     * @param string $error
     * @return Mage_Api2_Model_Resource_Validator
     */
    protected function _addError($error)
    {
        $this->_errors[] = $error;
        return $this;
    }

    /**
     * Returns an array of errors that explain why the most recent isValidData()
     * call returned false. The array keys are validation failure error identifiers,
     * and the array values are the corresponding human-readable error strings.
     *
     * If isValidData() was never called or if the most recent isValidData() call
     * returned true, then this method returns an empty array.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }
}
