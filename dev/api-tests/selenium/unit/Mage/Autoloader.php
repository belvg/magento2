<?php
/**
 * {license_notice}
 *
 * @category    tests
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Simple autoloader implementation
 *
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @author      Magento Core Team <core@magentocommerce.com>
 * @license     {license_link}
 */
class Mage_Autoloader
{
    /**
     * Registers the autoloader handler
     */
    public static function register()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Autoload handler implementation
     *
     * @param string $className Class name to be loaded, e.g. Mage_Selenium_TestCase
     * @return boolean
     */
    public static function autoload($className)
    {
        $classFile = str_replace(' ', DIRECTORY_SEPARATOR, ucwords(str_replace('_', ' ', $className)));
        $classFile = $classFile . '.php';
        return include_once $classFile;
    }
}