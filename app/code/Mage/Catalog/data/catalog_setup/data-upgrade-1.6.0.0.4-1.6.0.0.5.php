<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */

/** @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;

/** @var $eavResource Mage_Catalog_Model_Resource_Eav_Attribute */
$eavResource = Mage::getResourceModel('Mage_Catalog_Model_Resource_Eav_Attribute');

$multiSelectAttributeCodes = $eavResource->getAttributeCodesByFrontendType('multiselect');

foreach($multiSelectAttributeCodes as $attributeCode) {
    /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
    $attribute = $installer->getAttribute('catalog_product', $attributeCode);
    if ($attribute) {
        $attributeTable = $installer->getAttributeTable('catalog_product', $attributeCode);
        $select = $installer->getConnection()->select()
            ->from(array('e' => $attributeTable))
            ->where("e.attribute_id=?", $attribute['attribute_id'])
            ->where('e.value LIKE "%,,%"');
        $result = $installer->getConnection()->fetchAll($select);

        if ($result) {
            foreach ($result as $row) {
                if (isset($row['value']) && !empty($row['value'])) {
                    // 1,2,,,3,5 --> 1,2,3,5
                    $row['value'] = preg_replace('/,{2,}/', ',', $row['value'], -1, $replaceCnt);

                    if ($replaceCnt) {
                        $installer->getConnection()
                            ->update($attributeTable, array('value' => $row['value']), "value_id=" . $row['value_id']);
                    }
                }
            }
        }
    }
}