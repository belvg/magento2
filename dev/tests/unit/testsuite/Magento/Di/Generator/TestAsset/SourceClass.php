<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Di
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Di\Generator\TestAsset;
use Zend\Code\Generator\ClassGenerator;

class SourceClass extends ParentClass
{
    /**
     * Public child constructor
     *
     * @param \Zend\Code\Generator\ClassGenerator $classGenerator
     * @param string $param1
     * @param string $param2
     * @param string $param3
     * @param null $param4
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(ClassGenerator $classGenerator, $param1 = '', $param2 = '\\', $param3 = '\'',
        $param4 = null
    ) {
    }

    /**
     * Public child method
     *
     * @param \Zend\Code\Generator\ClassGenerator $classGenerator
     * @param string $param1
     * @param string $param2
     * @param string $param3
     * @param array $array
     * @param null $param5
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function publicChildMethod(ClassGenerator $classGenerator, $param1 = '', $param2 = '\\', $param3 = '\'',
        array $array = array(), $param5 = null
    ) {
    }

    /**
     * Public child method with reference
     *
     * @param \Zend\Code\Generator\ClassGenerator $classGenerator
     * @param array $array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function publicMethodWithReference(ClassGenerator &$classGenerator, array &$array)
    {
    }

    /**
     * Protected child method
     *
     * @param \Zend\Code\Generator\ClassGenerator $classGenerator
     * @param string $param1
     * @param string $param2
     * @param string $param3
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _protectedChildMethod(ClassGenerator $classGenerator, $param1 = '', $param2 = '\\',
        $param3 = '\''
    ) {
    }

    /**
     * Private child method
     *
     * @param \Zend\Code\Generator\ClassGenerator $classGenerator
     * @param string $param1
     * @param string $param2
     * @param string $param3
     * @param array $array
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function _privateChildMethod(ClassGenerator $classGenerator, $param1 = '', $param2 = '\\',
        $param3 = '\'', array $array = array()
    ) {
    }

    public function publicChildWithoutParameters()
    {
    }

    public static function publicChildStatic()
    {
    }

    final public function publicChildFinal()
    {
    }
}