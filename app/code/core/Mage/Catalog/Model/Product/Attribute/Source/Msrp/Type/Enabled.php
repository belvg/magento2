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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product MAP "Display Actual Price" attribute source
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Attribute_Source_Msrp_Type_Enabled extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Constants - attribute value
     */
    const MSRP_ENABLE_YES = 1;
    const MSRP_ENABLE_NO = 0;
    const MSRP_ENABLE_USE_CONFIG = 2;

    /**
     * Retrieve all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'label' => Mage::helper('catalog')->__('Yes'),
                    'value' => self::MSRP_ENABLE_YES
                ),
                array(
                    'label' => Mage::helper('catalog')->__('No'),
                    'value' => self::MSRP_ENABLE_NO
                ),
                array(
                    'label' => Mage::helper('catalog')->__('Use config'),
                    'value' => self::MSRP_ENABLE_USE_CONFIG
                )
            );
        }
        return $this->_options;
    }
}