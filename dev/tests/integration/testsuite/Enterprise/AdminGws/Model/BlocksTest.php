<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Enterprise_AdminGws
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Enterprise_AdminGws_Model_BlocksTest extends Magento_Test_TestCase_ControllerAbstract
{
    protected function setUp()
    {
        parent::setUp();
        Mage::setCurrentArea('adminhtml');
        /** @var $auth Mage_Backend_Model_Auth */
        Mage::getSingleton('Mage_Backend_Model_Url')->turnOffSecretKey();
        $auth = Mage::getSingleton('Mage_Backend_Model_Auth');
        $auth->login('admingws_user', 'admingws_password1');
    }

    protected function tearDown()
    {
        /** @var $auth Mage_Backend_Model_Auth */
        $auth = Mage::getSingleton('Mage_Backend_Model_Auth');
        $auth->logout();
        Mage::getSingleton('Mage_Backend_Model_Url')->turnOnSecretKey();
        parent::tearDown();
    }

    /**
     * @magentoConfigFixture admin_store catalog/enterprise_catalogpermissions/enabled 1
     * @magentoDataFixture Mage/Catalog/_files/categories.php
     * @magentoDataFixture Enterprise/AdminGws/_files/role_websites_login.php
     */
    public function testValidateCatalogPermissionsWebsites()
    {
        $this->dispatch('backend/admin/catalog_category/edit/id/3');
        $result = $this->getResponse()->getBody();
        $this->assertContains('category_permissions_3', $result);
        $this->assertContains('limited_website_ids', $result);
    }

    /**
     * @magentoConfigFixture admin_store catalog/enterprise_catalogpermissions/enabled 1
     * @magentoDataFixture Mage/Catalog/_files/categories.php
     * @magentoDataFixture Enterprise/AdminGws/_files/role_stores_login.php
     */
    public function testValidateCatalogPermissionsStoreGroups()
    {
        $this->dispatch('backend/admin/catalog_category/edit/id/3');
        $this->assertContains(
            'title="New Permission" type="button" class="scalable delete disabled disabled" disabled="disabled"',
            $this->getResponse()->getBody()
        );
    }

    /**
     * @magentoDataFixture Enterprise/AdminGws/_files/role_websites_login.php
     */
    public function testBackendUserRoleEditContainsGwsBlock()
    {
        $this->dispatch('backend/admin/user_role/editrole');

        $this->assertInstanceOf(
            'Enterprise_AdminGws_Block_Adminhtml_Permissions_Tab_Rolesedit_Gws',
            Mage::app()->getLayout()->getBlock('adminhtml.user.role.edit.gws'),
            'Enterprise_AdminGws_Block_Adminhtml_Permissions_Tab_Rolesedit_Gws block is not loaded'
        );

        $result = $this->getResponse()->getBody();
        $expected = '<h4 class="icon-head head-edit-form fieldset-legend">Role Scopes</h4>';
        $this->assertContains(
            $expected,
            $result,
            'Enterprise_AdminGws_Block_Adminhtml_Permissions_Tab_Rolesedit_Gws block is not rendered'
        );
    }

    /**
     * @magentoDataFixture Enterprise/AdminGws/_files/role_websites_login.php
     */
    public function testBackendUserRoleEditRoleGridContainsGwsBlock()
    {
        $this->dispatch('backend/admin/user_role/editrolegrid');

        $this->assertInstanceOf(
            'Enterprise_AdminGws_Block_Adminhtml_Permissions_Grid_Role',
            Mage::app()->getLayout()->getBlock('adminhtml.user.role.grid'),
            'Enterprise_AdminGws_Block_Adminhtml_Permissions_Grid_Role block is not loaded'
        );
    }
}
