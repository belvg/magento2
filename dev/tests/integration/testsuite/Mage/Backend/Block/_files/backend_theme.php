<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/** @var $registration Mage_Core_Model_Theme_Registration */
Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_ADMINHTML, Mage_Core_Model_App_Area::PART_CONFIG);
$registration = Mage::getModel('Mage_Core_Model_Theme_Registration');
$registration->register(
    __DIR__ . DIRECTORY_SEPARATOR . 'design',
    implode(DIRECTORY_SEPARATOR, array('*', '*', '*', 'theme.xml'))
);
