<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Bundle Price View Attribute Renderer
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Bundle_Model_Product_Attribute_Source_Price_View extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => Mage::helper('Mage_Bundle_Helper_Data')->__('As Low as'),
                    'value' =>  1
                ),
                array(
                    'label' => Mage::helper('Mage_Bundle_Helper_Data')->__('Price Range'),
                    'value' =>  0
                ),
            );
        }
        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColums()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $column = array(
            'unsigned'  => false,
            'default'   => null,
            'extra'     => null
        );

        if (Mage::helper('Mage_Core_Helper_Data')->useDbCompatibleMode()) {
            $column['type']     = 'int';
            $column['is_null']  = true;
        } else {
            $column['type']     = Varien_Db_Ddl_Table::TYPE_INTEGER;
            $column['nullable'] = true;
            $column['comment']  = 'Bundle Price View ' . $attributeCode . ' column';
        }

        return array($attributeCode => $column);
   }

    /**
     * Retrieve Select for update Attribute value in flat table
     *
     * @param   int $store
     * @return  Varien_Db_Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Option')
            ->getFlatUpdateSelect($this->getAttribute(), $store, false);
    }
}