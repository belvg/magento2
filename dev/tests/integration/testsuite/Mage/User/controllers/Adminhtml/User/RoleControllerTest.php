<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_User
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for Mage_User_Adminhtml_User_RoleController.
 *
 * @group module:Mage_User
 */
class Mage_User_Adminhtml_User_RoleControllerTest extends Mage_Adminhtml_Utility_Controller
{
    public function testEditRoleAction()
    {
        $this->markTestIncomplete('MAGETWO-1587');
        $roleAdmin = new Mage_User_Model_Role();
        $roleAdmin->load(Magento_Test_Bootstrap::ADMIN_ROLE_NAME, 'role_name');

        $this->getRequest()->setParam('rid', $roleAdmin->getId());

        $this->dispatch('admin/user_role/editrole');

        $this->assertContains('Role Information', $this->getResponse()->getBody());
        $this->assertContains("Edit Role '" . $roleAdmin->getRoleName() . "'", $this->getResponse()->getBody());
    }

    /**
     * @covers Mage_User_Adminhtml_User_RoleController::editrolegridAction
     */
    public function testEditrolegridAction()
    {
        $this->markTestIncomplete('MAGETWO-1587');
        $this->getRequest()
            ->setParam('ajax', true)
            ->setParam('isAjax', true);
        $this->dispatch('admin/user_role/editrolegrid');
        $expected = '%a<table %a id="roleUserGrid_table">%a';
        $this->assertStringMatchesFormat($expected, $this->getResponse()->getBody());
    }

    /**
     * @covers Mage_User_Adminhtml_User_RoleController::roleGridAction
     */
    public function testRoleGridAction()
    {
        $this->markTestIncomplete('MAGETWO-1587');
        $this->getRequest()
            ->setParam('ajax', true)
            ->setParam('isAjax', true)
            ->setParam('user_id', 1);
        $this->dispatch('admin/user_role/roleGrid');
        $expected = '%a<table %a id="roleGrid_table">%a';
        $this->assertStringMatchesFormat($expected, $this->getResponse()->getBody());
    }
}
