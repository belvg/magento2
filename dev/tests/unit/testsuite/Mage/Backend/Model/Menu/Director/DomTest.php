<?php
    /**
     * {license_notice}
     *
     * @category    Magento
     * @package     Mage_Backend
     * @subpackage  unit_tests
     * @copyright   {copyright}
     * @license     {license_link}
     */

/**
 * Test class for Mage_Backend_Model_Menu_Director_Dom
 */
class Mage_Backend_Model_Menu_Director_DomTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Mage_Backend_Model_Menu_Director_Dom
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_loggerMock;

    public function setUp()
    {
        $basePath = realpath(__DIR__)  . '/../../_files/';
        $path = $basePath . 'menu_merged.xml';
        $domDocument = new DOMDocument();
        $domDocument->load($path);

        $mockCommand = $this->getMockForAbstractClass(
            'Mage_Backend_Model_Menu_Builder_CommandAbstract',
            array(),
            '',
            false,
            true,
            true,
            array('getId')
        );

        $factory = $this->getMock('Mage_Core_Model_Config', array(), array(), '', false);
        $factory->expects($this->any())->method('getModelInstance')->will($this->returnValue($mockCommand));

        $this->_loggerMock = $this->getMock('Mage_Backend_Model_Menu_Logger', array('log'));

        $this->_model = new Mage_Backend_Model_Menu_Director_Dom(
            array(
                'config' => $domDocument,
                'factory' => $factory,
                'logger' => $this->_loggerMock
            )
        );
    }

    /**
     * Test __construct if required param missed
     * @expectedException InvalidArgumentException
     */
    public function testInvalidConstructorException()
    {
        new Mage_Backend_Model_Menu_Director_Dom();
    }

    /**
     * Test __construct if config is no instance of DOMDocument
     * @expectedException InvalidArgumentException
     */
    public function testInvalidConfigInstanceConstructorException()
    {
        $object = $this->getMock('StdClass');
        new Mage_Backend_Model_Menu_Director_Dom(array('config' => $object, 'factory' => $object));
    }

    /**
     * Test __construct
     *
     * @expectedException InvalidArgumentException
     */
    public function testMissingLoggerInstanceException()
    {
        $domDocument = $this->getMock('DOMDocument');
        $factory = $this->getMock('Mage_Core_Model_Config', array(), array(), '', false);
        $logger = null;
        $model = new Mage_Backend_Model_Menu_Director_Dom(
            array(
                'config' => $domDocument,
                'factory' => $factory,
                'logger' => $logger
            )
        );

        unset($model);
    }

    /**
     * Test __construct
     *
     * @expectedException InvalidArgumentException
     */
    public function testInvalidLoggerInstanceException()
    {
        $domDocument = $this->getMock('DOMDocument');
        $factory = $this->getMock('Mage_Core_Model_Config', array(), array(), '', false);
        $logger = $this->getMock('StdClass');
        $model = new Mage_Backend_Model_Menu_Director_Dom(
            array(
                'config' => $domDocument,
                'factory' => $factory,
                'logger' => $logger
            )
        );

        unset($model);
    }

    /**
     * Test __construct if config is instance of DOMDocument
     */
    public function testValidConfigInstanceConstructor()
    {
        $domDocument = $this->getMock('DOMDocument');
        $factory = $this->getMock('Mage_Core_Model_Config', array(), array(), '', false);
        $logger = $this->getMock('Mage_Backend_Model_Menu_Logger');
        $model = new Mage_Backend_Model_Menu_Director_Dom(
            array(
                'config' => $domDocument,
                'factory' => $factory,
                'logger' => $logger
            )
        );
        unset($model);
    }

    /**
     * Test data extracted from DOMDocument
     */
    public function testExtractData()
    {
        $basePath = realpath(__DIR__)  . '/../../_files/';
        $expectedData = include ($basePath . 'menu_merged.php');
        $this->assertEquals($expectedData, $this->_model->getExtractedData(), 'Invalid extracted data');
    }

    /**
     * Test command method with valid builder
     */
    public function testCommandWithValidBuilder()
    {
        $builder = $this->getMock('Mage_Backend_Model_Menu_Builder', array('processCommand'), array(), '', false);
        $builder->expects($this->exactly(8))->method('processCommand');
        $this->assertInstanceOf('Mage_Backend_Model_Menu_DirectorAbstract', $this->_model->buildMenu($builder));
    }

    public function testCommandLogging()
    {
        $this->_loggerMock->expects($this->exactly(4))->method('log');
        $builder = $this->getMock('Mage_Backend_Model_Menu_Builder', array(), array(), '', false);
        $this->_model->buildMenu($builder);
    }
}
