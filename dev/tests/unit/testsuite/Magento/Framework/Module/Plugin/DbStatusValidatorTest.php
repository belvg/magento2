<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Module\Plugin;

class DbStatusValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\Plugin\DbStatusValidator
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dbUpdaterMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleManager;

    protected function setUp()
    {
        $this->_cacheMock = $this->getMock('\Magento\Framework\Cache\FrontendInterface');
        $this->_dbUpdaterMock = $this->getMock('\Magento\Framework\Module\Updater', [], [], '', false);
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $this->subjectMock = $this->getMock('Magento\Framework\App\FrontController', [], [], '', false);
        $moduleList = $this->getMockForAbstractClass('\Magento\Framework\Module\ModuleListInterface');
        $moduleList->expects($this->any())
            ->method('getNames')
            ->will($this->returnValue(['Module_One', 'Module_Two']));
        $resourceResolver = $this->getMockForAbstractClass('\Magento\Framework\Module\ResourceResolverInterface');
        $resourceResolver->expects($this->any())
            ->method('getResourceList')
            ->will($this->returnCallback(function ($moduleName) {
                return ['resource_' . $moduleName];
            }));
        $this->moduleManager = $this->getMock('\Magento\Framework\Module\Manager', [], [], '', false);
        $this->_model = new DbStatusValidator(
            $this->_cacheMock,
            $moduleList,
            $resourceResolver,
            $this->moduleManager
        );
    }

    public function testAroundDispatch()
    {
        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('db_is_up_to_date')
            ->will($this->returnValue(false));
        $returnMap = [
            ['Module_One', 'resource_Module_One', true],
            ['Module_Two', 'resource_Module_Two', true],
        ];
        $this->moduleManager->expects($this->any())
            ->method('isDbSchemaUpToDate')
            ->will($this->returnValueMap($returnMap));
        $this->moduleManager->expects($this->any())
            ->method('isDbDataUpToDate')
            ->will($this->returnValueMap($returnMap));

        $this->assertEquals(
            'Expected',
            $this->_model->aroundDispatch($this->subjectMock, $this->closureMock, $this->requestMock)
        );
    }

    public function testAroundDispatchCached()
    {
        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('db_is_up_to_date')
            ->will($this->returnValue(true));
        $this->moduleManager->expects($this->never())
            ->method('isDbSchemaUpToDate');
        $this->moduleManager->expects($this->never())
            ->method('isDbDataUpToDate');
        $this->assertEquals(
            'Expected',
            $this->_model->aroundDispatch($this->subjectMock, $this->closureMock, $this->requestMock)
        );
    }

    /**
     * @param array $schemaValueMap
     * @param array $dataValueMap
     *
     * @dataProvider aroundDispatchExceptionDataProvider
     * @expectedException \Magento\Framework\Module\Exception
     * @expectedExceptionMessage Looks like database is outdated. Please, use setup tool to perform update
     */
    public function testAroundDispatchException(array $schemaValueMap, array $dataValueMap)
    {
        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('db_is_up_to_date')
            ->will($this->returnValue(false));
        $this->_cacheMock->expects($this->never())->method('save');
        $this->moduleManager->expects($this->any())
            ->method('isDbSchemaUpToDate')
            ->will($this->returnValueMap($schemaValueMap));
        $this->moduleManager->expects($this->any())
            ->method('isDbDataUpToDate')
            ->will($this->returnValueMap($dataValueMap));
        $this->_model->aroundDispatch($this->subjectMock, $this->closureMock, $this->requestMock);
    }

    /**
     * @return array
     */
    public function aroundDispatchExceptionDataProvider()
    {
        return [
            'schema is outdated' => [
                [
                    ['Module_One', 'resource_Module_One', false],
                    ['Module_Two', 'resource_Module_Two', true],
                ],
                [
                    ['Module_One', 'resource_Module_One', true],
                    ['Module_Two', 'resource_Module_Two', true],
                ],
            ],
            'data is outdated' => [
                [
                    ['Module_One', 'resource_Module_One', true],
                    ['Module_Two', 'resource_Module_Two', true],
                ],
                [
                    ['Module_One', 'resource_Module_One', true],
                    ['Module_Two', 'resource_Module_Two', false],
                ],
            ],
            'both schema and data are outdated' => [
                [
                    ['Module_One', 'resource_Module_One', false],
                    ['Module_Two', 'resource_Module_Two', false],
                ],
                [
                    ['Module_One', 'resource_Module_One', false],
                    ['Module_Two', 'resource_Module_Two', false],
                ],
            ],
        ];
    }
}
