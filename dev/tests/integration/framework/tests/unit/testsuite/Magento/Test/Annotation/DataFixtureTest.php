<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for Magento_Test_Annotation_DataFixture.
 *
 * @magentoDataFixture sampleFixtureOne
 */
class Magento_Test_Annotation_DataFixtureTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Test_Annotation_DataFixture|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = $this->getMock(
            'Magento_Test_Annotation_DataFixture', array('_applyOneFixture'), array(__DIR__ . '/_files')
        );
    }

    public static function sampleFixtureOne()
    {
    }

    public static function sampleFixtureTwo()
    {
    }

    public static function sampleFixtureTwoRollback()
    {
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testConstructorException()
    {
        new Magento_Test_Annotation_DataFixture(__DIR__ . '/non_existing_fixture_dir');
    }

    public function testStartTestTransactionRequestClassAnnotation()
    {
        $eventParam = new Magento_Test_Event_Param_Transaction();
        $this->_object->startTestTransactionRequest($this, $eventParam);
        $this->assertTrue($eventParam->isTransactionBeginRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());

        $eventParam = new Magento_Test_Event_Param_Transaction();
        $this->_object->beginTransaction($this);
        $this->_object->startTestTransactionRequest($this, $eventParam);
        $this->assertFalse($eventParam->isTransactionBeginRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());
    }

    /**
     * @magentoDataFixture sampleFixtureTwo
     * @magentoDataFixture path/to/fixture/script.php
     */
    public function testStartTestTransactionRequestMethodAnnotation()
    {
        $eventParam = new Magento_Test_Event_Param_Transaction();
        $this->_object->startTestTransactionRequest($this, $eventParam);
        $this->assertTrue($eventParam->isTransactionBeginRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());

        $eventParam = new Magento_Test_Event_Param_Transaction();
        $this->_object->beginTransaction($this);
        $this->_object->startTestTransactionRequest($this, $eventParam);
        $this->assertTrue($eventParam->isTransactionBeginRequested());
        $this->assertTrue($eventParam->isTransactionRollbackRequested());
    }

    /**
     * @magentoDataFixture fixture\path\must\not\contain\backslash.php
     * @expectedException Magento_Exception
     */
    public function testStartTestTransactionRequestInvalidPath()
    {
        $this->_object->startTestTransactionRequest($this, new Magento_Test_Event_Param_Transaction());
    }

    /**
     * @magentoDataFixture sampleFixtureTwo
     * @magentoDataFixture path/to/fixture/script.php
     */
    public function testEndTestTransactionRequestMethodAnnotation()
    {
        $eventParam = new Magento_Test_Event_Param_Transaction();
        $this->_object->endTestTransactionRequest($this, $eventParam);
        $this->assertFalse($eventParam->isTransactionBeginRequested());
        $this->assertFalse($eventParam->isTransactionRollbackRequested());

        $eventParam = new Magento_Test_Event_Param_Transaction();
        $this->_object->beginTransaction($this);
        $this->_object->endTestTransactionRequest($this, $eventParam);
        $this->assertFalse($eventParam->isTransactionBeginRequested());
        $this->assertTrue($eventParam->isTransactionRollbackRequested());
    }

    public function testBeginTransactionClassAnnotation()
    {
        $this->_object
            ->expects($this->once())
            ->method('_applyOneFixture')
            ->with(array(__CLASS__, 'sampleFixtureOne'))
        ;
        $this->_object->beginTransaction($this);
    }

    /**
     * @magentoDataFixture sampleFixtureTwo
     * @magentoDataFixture path/to/fixture/script.php
     */
    public function testBeginTransactionMethodAnnotation()
    {
        $this->_object
            ->expects($this->at(0))
            ->method('_applyOneFixture')
            ->with(array(__CLASS__, 'sampleFixtureTwo'))
        ;
        $this->_object
            ->expects($this->at(1))
            ->method('_applyOneFixture')
            ->with($this->stringEndsWith('path/to/fixture/script.php'))
        ;
        $this->_object->beginTransaction($this);
    }

    /**
     * @magentoDataFixture sampleFixtureOne
     * @magentoDataFixture sampleFixtureTwo
     */
    public function testRollbackTransactionRevertFixtureMethod()
    {
        $this->_object->beginTransaction($this);
        $this->_object
            ->expects($this->once())
            ->method('_applyOneFixture')
            ->with(array(__CLASS__, 'sampleFixtureTwoRollback'))
        ;
        $this->_object->rollbackTransaction();
    }


    /**
     * @magentoDataFixture path/to/fixture/script.php
     * @magentoDataFixture sample_fixture_two.php
     */
    public function testRollbackTransactionRevertFixtureFile()
    {
        $this->_object->beginTransaction($this);
        $this->_object
            ->expects($this->once())
            ->method('_applyOneFixture')
            ->with($this->stringEndsWith('sample_fixture_two_rollback.php'))
        ;
        $this->_object->rollbackTransaction();
    }
}