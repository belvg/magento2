<?php
/**
 * Test class for Mage_Webapi_Model_Acl_User
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Webapi_Model_Acl_UserTest extends PHPUnit_Framework_TestCase
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
     * @var Mage_Webapi_Model_Resource_Acl_User|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_userResource;

    protected function setUp()
    {
        $this->_helper = new Magento_Test_Helper_ObjectManager($this);

        $this->_objectManager = $this->getMockBuilder('Magento_ObjectManager')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMockForAbstractClass();

        $this->_userResource = $this->getMockBuilder('Mage_Webapi_Model_Resource_Acl_User')
            ->disableOriginalConstructor()
            ->setMethods(array('getIdFieldName', 'getRoleUsers', 'load', 'getReadConnection'))
            ->getMock();

        $this->_userResource->expects($this->any())
            ->method('getIdFieldName')
            ->withAnyParameters()
            ->will($this->returnValue('id'));

        $this->_userResource->expects($this->any())
            ->method('getReadConnection')
            ->withAnyParameters()
            ->will($this->returnValue($this->getMock('Varien_Db_Adapter_Pdo_Mysql', array(), array(), '', false)));
    }

    /**
     * Create User model
     *
     * @param Mage_Webapi_Model_Resource_Acl_User $userResource
     * @param Mage_Webapi_Model_Resource_Acl_User_Collection $resourceCollection
     * @return Mage_Webapi_Model_Acl_User
     */
    protected function _createModel($userResource, $resourceCollection = null)
    {
        return $this->_helper->getModel('Mage_Webapi_Model_Acl_User', array(
            'eventDispatcher' => $this->getMock('Mage_Core_Model_Event_Manager', array(), array(), '', false),
            'cacheManager' => $this->getMock('Mage_Core_Model_Cache', array(), array(), '', false),
            'resource' => $userResource,
            'resourceCollection' => $resourceCollection
        ));
    }

    /**
     * Test constructor
     */
    public function testConstructor()
    {
        $model = $this->_createModel($this->_userResource);

        $this->assertAttributeEquals('Mage_Webapi_Model_Resource_Acl_User', '_resourceName', $model);
        $this->assertAttributeEquals('id', '_idFieldName', $model);
    }

    /**
     * Test method getRoleUsers()
     */
    public function testGetRoleUsers()
    {
        $this->_userResource->expects($this->once())
            ->method('getRoleUsers')
            ->with(1)
            ->will($this->returnValue(array(1)));

        $model = $this->_createModel($this->_userResource);

        $result = $model->getRoleUsers(1);

        $this->assertEquals(array(1), $result);
    }

    /**
     * Test method loadByKey()
     */
    public function testLoadByKey()
    {
        $this->_userResource->expects($this->once())
            ->method('load')
            ->with($this->anything(), 'key', 'api_key')
            ->will($this->returnSelf());

        $model = $this->_createModel($this->_userResource);

        $result = $model->loadByKey('key');
        $this->assertInstanceOf('Mage_Webapi_Model_Acl_User', $result);
    }

    /**
     * Test public getters
     */
    public function testPublicGetters()
    {
        $model = $this->_createModel($this->_userResource);

        $model->setData('secret', 'secretKey');

        $this->assertEquals('secretKey', $model->getSecret());
        $this->assertEquals('', $model->getCallBackUrl());
    }

    /**
     * Test get collection and _construct
     */
    public function testGetCollection()
    {
        /** @var Mage_Webapi_Model_Resource_Acl_User_Collection $collection */
        $collection = $this->getMockBuilder('Mage_Webapi_Model_Resource_Acl_User_Collection')
            ->setConstructorArgs(array('resource' => $this->_userResource))
            ->setMethods(array('_initSelect'))
            ->getMock();

        $collection->expects($this->any())
            ->method('_initSelect')
            ->withAnyParameters()
            ->will($this->returnValue(null));

        $model = $this->_createModel($this->_userResource, $collection);
        $result = $model->getCollection();

        $this->assertAttributeEquals('Mage_Webapi_Model_Acl_User', '_model', $result);
        $this->assertAttributeEquals('Mage_Webapi_Model_Resource_Acl_User', '_resourceModel', $result);
    }
}