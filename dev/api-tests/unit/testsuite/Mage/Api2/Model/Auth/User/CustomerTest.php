<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Webapi
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Webapi User Customer Mock Class
 *
 * @category   Mage
 * @package    Mage_Webapi
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Webapi_Model_Auth_User_Customer_Mock extends Mage_Webapi_Model_Auth_User_Customer
{
    /**
     * User Role rewrite for test purposes
     *
     * @var string
     */
    public $_role;

    /**
     * Set user role
     *
     * @param int $role
     * @return Mage_Webapi_Model_Auth_User_Customer_Mock
     */
    public function setRole($role)
    {
        $this->_role = $role;

        return $this;
    }
}

/**
 * Test Webapi User Customer model
 */
class Mage_Webapi_Model_Auth_User_CustomerTest extends Mage_PHPUnit_TestCase
{
    /**
     * Webapi User Customer model
     *
     * @var Mage_Webapi_Model_Auth_User_Customer
     */
    protected $_customer;

    /**
     * Webapi Role model mock
     *
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_roleMock;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->_customer = Mage::getModel('Mage_Webapi_Model_Auth_User_Customer');
        $this->_roleMock = $this->getModelMockBuilder('Mage_Webapi_Model_Acl_Global_Role')
            ->setMethods(array('load', 'getId'))
            ->getMock();
    }

    /**
     * Test guest role ID
     */
    public function testGuestRoleId()
    {
        $this->assertInternalType('integer', Mage_Webapi_Model_Acl_Global_Role::ROLE_CUSTOMER_ID);
    }

    /**
     * Test getRole method
     */
    public function testGetRole()
    {
        $this->_roleMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->_roleMock));

        $this->_roleMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(Mage_Webapi_Model_Acl_Global_Role::ROLE_CUSTOMER_ID));

        $this->assertEquals(Mage_Webapi_Model_Acl_Global_Role::ROLE_CUSTOMER_ID, $this->_customer->getRole());
    }

    /**
     * Test getRole method second call
     */
    public function testGetRoleSecondCall()
    {
        $this->_roleMock->expects($this->never())
            ->method('load');

        $this->_roleMock->expects($this->never())
            ->method('getId');

        $customerMock = new Mage_Webapi_Model_Auth_User_Customer_Mock;
        $customerMock->setRole(Mage_Webapi_Model_Acl_Global_Role::ROLE_CUSTOMER_ID);

        $this->assertEquals(Mage_Webapi_Model_Acl_Global_Role::ROLE_CUSTOMER_ID, $customerMock->getRole());
    }

    /**
     * Test getRole method
     */
    public function testGetRoleNotFound()
    {
        $this->_roleMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->_roleMock));

        $this->_roleMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(null));

        try {
            $this->_customer->getRole();
        } catch (Exception $e) {
            $this->assertEquals('Customer role not found', $e->getMessage(), 'Invalid exception message');

            return;
        }
        $this->fail('An expected exception has not been raised.');
    }

    /**
     * Test getType method
     */
    public function testGetType()
    {
        $this->assertEquals('customer', Mage::getModel('Mage_Webapi_Model_Auth_User_Customer')->getType());
    }
}
