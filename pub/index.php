<?php
/**
 * Public alias for the application entry point
 *
 * {license_notice}
 *
 * @copyright  {copyright}
 * @license    {license_link}
 */
require __DIR__ . '/../app/bootstrap.php';
Magento_Profiler::start('mage');
$params = $_SERVER;
$params[Mage::PARAM_APP_URIS][Mage_Core_Model_Dir::PUB] = '';
$entryPoint = new Mage_Core_Model_EntryPoint_Http(BP, $params);
$entryPoint->processRequest();
Magento_Profiler::stop('mage');
