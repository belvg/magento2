<?php
/**
 * Test class for Mage_Webapi_Model_Acl_Rule
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Webapi_Model_Acl_RuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Test_Helper_ObjectManager
     */
    protected $_helper;

    /**
     * @var Magento_ObjectManager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var Mage_Webapi_Model_Resource_Acl_Rule|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_ruleResource;

    protected function setUp()
    {
        $this->_helper = new Magento_Test_Helper_ObjectManager($this);

        $this->_objectManager = $this->getMockBuilder('Magento_ObjectManager')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMockForAbstractClass();

        $this->_ruleResource = $this->getMockBuilder('Mage_Webapi_Model_Resource_Acl_Rule')
            ->disableOriginalConstructor()
            ->setMethods(array('saveResources', 'getIdFieldName', 'getReadConnection'))
            ->getMock();

        $this->_ruleResource->expects($this->any())
            ->method('getIdFieldName')
            ->withAnyParameters()
            ->will($this->returnValue('id'));

        $this->_ruleResource->expects($this->any())
            ->method('getReadConnection')
            ->withAnyParameters()
            ->will($this->returnValue($this->getMock('Varien_Db_Adapter_Pdo_Mysql', array(), array(), '', false)));
    }

    /**
     * Create Rule model
     *
     * @param Mage_Webapi_Model_Resource_Acl_Rule|PHPUnit_Framework_MockObject_MockObject $ruleResource
     * @param Mage_Webapi_Model_Resource_Acl_User_Collection $resourceCollection
     * @return Mage_Webapi_Model_Acl_Rule
     */
    protected function _createModel($ruleResource, $resourceCollection = null)
    {
        return $this->_helper->getModel('Mage_Webapi_Model_Acl_Rule', array(
            'eventDispatcher' => $this->getMock('Mage_Core_Model_Event_Manager', array(), array(), '', false),
            'cacheManager' => $this->getMock('Mage_Core_Model_Cache', array(), array(), '', false),
            'resource' => $ruleResource,
            'resourceCollection' => $resourceCollection
        ));
    }

    /**
     * Test constructor
     */
    public function testConstructor()
    {
        $model = $this->_createModel($this->_ruleResource);

        $this->assertAttributeEquals('Mage_Webapi_Model_Resource_Acl_Rule', '_resourceName', $model);
        $this->assertAttributeEquals('id', '_idFieldName', $model);
    }

    /**
     * Test method getRoleUsers()
     */
    public function testGetRoleUsers()
    {
        $this->_ruleResource->expects($this->once())
            ->method('saveResources')
            ->withAnyParameters()
            ->will($this->returnSelf());

        $model = $this->_createModel($this->_ruleResource);
        $result = $model->saveResources();
        $this->assertInstanceOf('Mage_Webapi_Model_Acl_Rule', $result);
    }

    /**
     * Test get collection and _construct
     */
    public function testGetCollection()
    {
        /** @var Mage_Webapi_Model_Resource_Acl_Rule_Collection $collection */
        $collection = $this->getMockBuilder('Mage_Webapi_Model_Resource_Acl_Rule_Collection')
            ->setConstructorArgs(array('resource' => $this->_ruleResource))
            ->setMethods(array('_initSelect', 'getSelect'))
            ->getMock();

        $collection->expects($this->any())
            ->method('_initSelect')
            ->withAnyParameters()
            ->will($this->returnValue(null));

        $collection->expects($this->any())
            ->method('getSelect')
            ->withAnyParameters()
            ->will($this->returnValue($this->getMock('Varien_Db_Select', array(), array(), '', false)));

        $model = $this->_createModel($this->_ruleResource, $collection);

        // test _construct
        $result = $model->getCollection();

        $this->assertAttributeEquals('Mage_Webapi_Model_Acl_Rule', '_model', $result);
        $this->assertAttributeEquals('Mage_Webapi_Model_Resource_Acl_Rule', '_resourceModel', $result);

        // test getByRole
        $resultColl = $result->getByRole(1);
        $this->assertInstanceOf('Mage_Webapi_Model_Resource_Acl_Rule_Collection', $resultColl);
    }
}
