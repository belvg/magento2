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
 * @package     Mage_CatalogInventory
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * API2 Stock Item Persist Validator
 *
 * @category   Mage
 * @package    Mage_CatalogInventory
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_CatalogInventory_Model_Api2_Stock_Item_Validator_Persist extends Mage_Api2_Model_Resource_Validator
{
    /**
     * The greatest value which could be stored in CatalogInventory Qty field
     */
    const MAX_QTY_VALUE = 99999999.9999;

    /**
     * Resource
     *
     * @var Mage_Api2_Model_Resource
     */
    protected $_resource;

    /**
     * Construct. Set all depends.
     *
     * Required parameteres for options:
     * - resource
     *
     * @param array $options
     * @throws Exception If passed parameter 'resource' is wrong
     */
    public function __construct($options)
    {
        if (!isset($options['resource']) || !$options['resource'] instanceof Mage_Api2_Model_Resource) {
            throw new Exception("Passed parameter 'resource' is wrong.");
        }
        $this->_resource = $options['resource'];
    }

    /**
     * Filter request data.
     *
     * @param  array $data
     * @return array Filtered data
     */
    public function filter(array $data)
    {
        if (!isset($data['use_config_manage_stock'])) {
            $data['use_config_manage_stock'] = 0;
        }
        if (isset($data['qty']) && (float)$data['qty'] > self::MAX_QTY_VALUE) {
            $data['qty'] = self::MAX_QTY_VALUE;
        }
        if (isset($data['min_qty']) && (int)$data['min_qty'] < 0) {
            $data['min_qty'] = 0;
        }
        if (!isset($data['is_decimal_divided']) || $data['is_qty_decimal'] == 0) {
            $data['is_decimal_divided'] = 0;
        }
        return $data;
    }

    /**
     * Validate IdField.
     * If fails validation, then this method returns false, and
     * getErrors() will return an array of errors that explain why the
     * validation failed.
     *
     * @param  array $data
     * @void bool
     */
    public function idFieldIsSatisfiedByData(array $data)
    {
        $hasError = false;
        $idField = $this->_resource->getIdFieldName();

        if (!array_key_exists($idField, $data)) {
            $this->_errors[] = sprintf('Missing "%s" in request.', $idField);
            $hasError = true;
        } else {
            if (trim($data[$idField]) == '') {
                $this->_errors[] = sprintf('Empty value for "%s" in request.', $idField);
                $hasError = true;
            }
        }

        return !$hasError;
    }

    /**
     * Validate data.
     * If fails validation, then this method returns false, and
     * getErrors() will return an array of errors that explain why the
     * validation failed.
     *
     * TODO: implement validation (https://jira.magento.com/browse/APIA-318)
     *
     * @param  array $data
     * @void bool
     */
    public function isSatisfiedByData(array $data)
    {
        $hasError = false;
        return !$hasError;
    }
}
