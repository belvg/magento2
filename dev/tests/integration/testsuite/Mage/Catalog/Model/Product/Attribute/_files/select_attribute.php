<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Catalog
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/** @var Mage_Eav_Model_Entity_Type $entityType */
$entityType = Mage::getModel('Mage_Eav_Model_Entity_Type');
$entityType->loadByCode('catalog_product');
$defaultSetId = $entityType->getDefaultAttributeSetId();
/** @var Mage_Eav_Model_Entity_Attribute_Set $defaultSet */
$defaultSet = Mage::getModel('Mage_Eav_Model_Entity_Attribute_Set');
$defaultSet->load($defaultSetId);
$defaultGroupId = $defaultSet->getDefaultGroupId();
$optionData = array(
    'value' => array(
        'option_1' => array(0 => 'Fixture Option'),
    ),
    'order' => array(
        'option_1' => 1,
    )
);

/** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
$attribute = Mage::getResourceModel('Mage_Catalog_Model_Resource_Eav_Attribute');
$attribute->setAttributeCode('select_attribute')
    ->setEntityTypeId($entityType->getEntityTypeId())
    ->setAttributeGroupId($defaultGroupId)
    ->setAttributeSetId($defaultSetId)
    ->setFrontendInput('select')
    ->setFrontendLabel('Select Attribute')
    ->setBackendType('int')
    ->setIsUserDefined(1)
    ->setOption($optionData)
    ->save();
