<?php
/**
 * Integrity test used to check, that all classes, written as direct class names in code, really exist
 *
 * {license_notice}
 *
 * @category    tests
 * @package     integration
 * @subpackage  integrity
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Integrity_ClassesTest extends PHPUnit_Framework_TestCase
{
    /**
     * List of methods in this class, that are designed to check file content.
     * Filled automatically via reflection.
     *
     * @var array
     */
    protected $_visitorMethods = null;

    /**
     * @param string $className
     * @dataProvider classExistsDataProvider
     */
    public function testClassExists($className)
    {
        if ($className == 'Mage_Catalog_Model_Resource_Convert') {
            $this->markTestIncomplete('Bug MAGE-4763');
        }
        $this->assertTrue(class_exists($className), 'Class ' . $className . ' does not exist');
    }

    /**
     * @return array
     */
    public function classExistsDataProvider()
    {
        $classNames = $this->_findAllClassNames();

        $result = array();
        foreach ($classNames as $className) {
            $result[] = array($className);
        }
        return $result;
    }

    /**
     * Gathers all class name definitions in Magento
     *
     * @return array
     */
    protected function _findAllClassNames()
    {
        $directory  = new RecursiveDirectoryIterator(Mage::getRoot());
        $iterator = new RecursiveIteratorIterator($directory);
        $regexIterator = new RegexIterator($iterator, '/(\.php|\.phtml)$/');

        $result = array();
        foreach ($regexIterator as $fileInfo) {
            $classNames = $this->_findClassNamesInFile($fileInfo);
            $result = array_merge($result, $classNames);
        }
        return $result;
    }

    /**
     * Gathers all class name definitions in a class
     *
     * @param SplFileInfo $fileInfo
     * @return array
     */
    protected function _findClassNamesInFile($fileInfo)
    {
        $content = file_get_contents((string) $fileInfo);

        $result = array();
        $visitorMethods = $this->_getVisitorMethods();
        foreach ($visitorMethods as $method) {
            $classNames = $this->$method($fileInfo, $content);
            if (!$classNames) {
                continue;
            }
            $classNames = array_combine($classNames, $classNames); // Thus array_merge will not have duplicates
            $result = array_merge($result, $classNames);
        }

        return $result;
    }

    /**
     * Returns all methods in this class, that are designed to visit the file content.
     * Protected methods starting with '_visit' are considered to be visitor methods.
     *
     * @return array
     */
    protected function _getVisitorMethods()
    {
        if ($this->_visitorMethods === null) {
            $this->_visitorMethods = array();
            $reflection = new ReflectionClass($this);
            foreach ($reflection->getMethods(ReflectionMethod::IS_PROTECTED) as $method) {
                if (substr($method->name, 0, 6) == '_visit') {
                    $this->_visitorMethods[] = $method->name;
                }
            }
        }

        return $this->_visitorMethods;
    }

    /**
     * Finds usage of Mage::getResourceModel('Class_Name'), Mage::getResourceSingleton('Class_Name')
     *
     * @param SplFileInfo $fileInfo
     * @param string $content
     * @return array
     */
    protected function _visitMageGetResource($fileInfo, $content)
    {
        if (!$this->_fileHasExtensions($fileInfo, array('php', 'phtml'))) {
            return array();
        }

        $funcNames = array('Mage::getResourceModel', 'Mage::getResourceSingleton');
        $result = array();
        foreach ($funcNames as $funcName) {
            $classNames = $this->_getFuncStringArguments($funcName, $content);
            $result = array_merge($result, $classNames);
        }

        return $result;
    }

    /**
     * Checks whether file path has required extension
     *
     * @param string|array $extensions
     * @return bool
     */
    protected function _fileHasExtensions($fileInfo, $extensions)
    {
        if (is_string($extensions)) {
            $extensions = array($extensions);
        }
        foreach ($extensions as $extension) {
            if ($fileInfo->getExtension() == $extension) {
                return true;
            }
        }
        return false;
    }

    /**
     * Finds all usages of function $funcName in $content, where it has only one constant string argument.
     * Returns array of all these arguments.
     *
     * @param string $funcName
     * @param string $content
     * @return array
     */
    protected function _getFuncStringArguments($funcName, $content)
    {
        $result = array();
        $matched = preg_match_all('/' . $funcName . '\([\'"]([^\'"]+)[\'"]\)/', $content, $matches);
        if ($matched) {
            $result = $matches[1];
        }
        return $result;
    }
}
