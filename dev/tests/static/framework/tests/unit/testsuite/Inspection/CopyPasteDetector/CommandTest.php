<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  static_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Inspection_CopyPasteDetector_CommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Inspection_CopyPasteDetector_Command|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cmd;

    protected function setUp()
    {
        $this->_cmd = $this->getMock(
            'Inspection_CopyPasteDetector_Command',
            array('_execShellCmd'),
            array('some/report/file.xml', 5, 50)
        );
    }

    /**
     * @dataProvider canRunDataProvider
     */
    public function testCanRun($cmdOutput, $expectedResult)
    {
        $this->_cmd
            ->expects($this->once())
            ->method('_execShellCmd')
            ->with($this->stringContains('phpcpd'))
            ->will($this->returnValue($cmdOutput))
        ;
        $this->assertEquals($expectedResult, $this->_cmd->canRun());
    }

    public function canRunDataProvider()
    {
        return array(
            'success' => array('phpcpd X.Y.Z', true),
            'failure' => array(false, false),
        );
    }

    /**
     * @dataProvider getVersionDataProvider
     */
    public function testGetVersion($versionCmdOutput, $expectedVersion)
    {
        $this->_cmd
            ->expects($this->once())
            ->method('_execShellCmd')
            ->with($this->stringContains('phpcpd'))
            ->will($this->returnValue($versionCmdOutput))
        ;
        $this->assertEquals($expectedVersion, $this->_cmd->getVersion());
    }

    public function getVersionDataProvider()
    {
        return array(
            array('phpcpd 1.3.2 by Sebastian Bergmann.', '1.3.2'),
        );
    }

    public function testRun()
    {
        $expectedQuoteChar = substr(escapeshellarg(' '), 0, 1);
        $expectedCmd = 'phpcpd'
            . ' --log-pmd "some/report/file.xml"'
            . ' --min-lines 5'
            . ' --min-tokens 50'
            . ' "some/test/dir with space" "some/test/file with space.php"'
        ;
        $expectedCmd = str_replace('"', $expectedQuoteChar, $expectedCmd);
        $this->_cmd
            ->expects($this->at(0))
            ->method('_execShellCmd')
            ->with($expectedCmd)
        ;
        $this->_cmd->run(array('some/test/dir with space', 'some/test/file with space.php'));
    }
}
