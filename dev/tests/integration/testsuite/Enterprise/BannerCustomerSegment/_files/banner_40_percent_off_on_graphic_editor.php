<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

require __DIR__ . '/../../../Mage/SalesRule/_files/cart_rule_40_percent_off.php';
require __DIR__ . '/../../../Enterprise/CustomerSegment/_files/segment_designers.php';

/** @var Mage_SalesRule_Model_Rule $rule */
$rule = Mage::getModel('Mage_SalesRule_Model_Rule');
$rule->load('40% Off on Large Orders', 'name');

/** @var $segment Enterprise_CustomerSegment_Model_Segment */
$segment = Mage::getModel('Enterprise_CustomerSegment_Model_Segment');
$segment->load('Designers', 'name');

/** @var Enterprise_Banner_Model_Banner $banner */
$banner = Mage::getModel('Enterprise_Banner_Model_Banner');
$banner->setData(array(
    'name' => 'Get 40% Off on Graphic Editors',
    'is_enabled' => Enterprise_Banner_Model_Banner::STATUS_ENABLED,
    'types' => array()/*Any Banner Type*/,
    'store_contents' => array('<img src="http://example.com/banner_40_percent_off_on_graphic_editor.png" />'),
    'banner_sales_rules' => array($rule->getId()),
    'customer_segment_ids' => array($segment->getId()),
));
$banner->save();