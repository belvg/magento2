<?php
/**
 * Magento
 *
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Store_EnableSingleStoreMode
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Community2_Mage_Store_EnableSingleStoreMode_DashboardTest extends Mage_Selenium_TestCase
{
    protected function assertPreconditions()
    {
        $this->loginAdminUser();
        $this->admin('manage_stores');
        $this->storeHelper()->deleteStoreViewsExceptSpecified(array('Default Store View'));
        $config = $this->loadDataSet('SingleStoreMode', 'enable_single_store_mode');
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($config);
    }

        public function tearDownAfterTest()
    {
        $this->loginAdminUser();
        $config = $this->loadDataSet('SingleStoreMode', 'disable_single_store_mode');
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($config);
    }

    /**
     * <p>Scope Selector is not displayed on the Dashboard page.</p>
     * <p>Steps:</p>
     * <p>1. Login to Backend.</p>
     * <p>2. Navigate to System - Manage Stores.</p>
     * <p>3. If there more one Store View - delete except Default Store View.</p>
     * <p>4. Navigate to Dashboard page.</p>
     * <p>Expected result:</p>
     * <p>There is no "Choose Store View" scope selector on the page.</p>
     *
     * @test
     * @TestlinkId TL-MAGE-6302
     * @author Nataliya_Kolenko
     */
    public function verificationDashboardPage()
    {
        $this->navigate('dashboard');
        $this->assertFalse($this->controlIsPresent('dropdown', 'store_switcher'),
            'There is "Choose Store View" scope selector on the page');
    }
}
