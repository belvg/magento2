<?php
/**
 * {license_notice}
 *
 * @category    Paas
 * @package     tests
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/** @var $entityType Mage_Eav_Model_Entity_Type */
$entityType = Mage::getModel('eav/entity_type')->loadByCode('catalog_product');
$taxClasses = Mage::getResourceModel('tax/class_collection')->toArray();
$taxClass = reset($taxClasses['items']);

return array(
    'type_id' => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
    'attribute_set_id' => $entityType->getDefaultAttributeSetId(),
    'sku' => 'simple' . uniqid(),
    'name' => 'Test',
    'description' => 'Test description',
    'short_description' => 'Test short description',
    'weight' => 125,
    'news_from_date' => '02/16/2012',
    'news_to_date' => '16.02.2012',
    'status' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
    'url_key' => '123!"№;%:?*()_+{}[]\|<>,.?/abc',
    'url_key_create_redirect' => 1,
    'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
    'price' => 25.50,
    'special_price' => 11.2,
    'special_from_date' => '02/16/2012',
    'special_to_date' => '03/17/2012',
    'group_price' => array(
        array('website_id' => 0, 'cust_group' => 1, 'price' => 11)
    ),
    'tier_price' => array(
        array('website_id' => 0,'cust_group' => 1, 'price_qty' => 5.5, 'price' => 11.054)
    ),
    'msrp_enabled' => 1,
    'msrp_display_actual_price_type' => 1,
    'msrp' => 11.015,
    'enable_googlecheckout' => 1,
    'tax_class_id' => $taxClass['class_id'],
    'meta_title' => 'Test title',
    'meta_keyword' => 'Test keyword',
    'meta_description' => str_pad('', 85, 'a4b'),
    'custom_design' => 'default/blank',
    'custom_design_from' => '02/16/2012',
    'custom_design_to' => '05/01/2012',
    'custom_layout_update' => '<xml><layout>Test Custom Layout Update</layout></xml>',
    'page_layout' => 'one_column',
    'options_container' => 'container1',
    'gift_message_available' => 1,
    'gift_wrapping_available' => 1,
    'gift_wrapping_price' => 0.99,
    'stock_data' => array(
        'manage_stock' => 1,
        'qty' => 1,
        'min_qty' => 1.56,
        'min_sale_qty' => 1,
        'max_sale_qty' => 1,
        'is_qty_decimal' => 0,
        'backorders' => 1,
        'notify_stock_qty' => -50.99,
        'enable_qty_increments' => 0,
        'is_in_stock' => 0
    )
);