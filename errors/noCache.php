<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Errors
 * @copyright   {copyright}
 * @license     {license_link}
 */

require_once 'processorSaas.php';

$processor = new Error_ProcessorSaas();
$processor->processNoCache();