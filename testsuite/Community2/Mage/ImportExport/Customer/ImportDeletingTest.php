<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer Tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @method Enterprise2_Mage_CustomerAttribute_Helper customerAttributeHelper() customerAttributeHelper()
 * @method Enterprise2_Mage_CustomerAddressAttribute_Helper customerAddressAttributeHelper() customerAddressAttributeHelper()
 * @method Enterprise2_Mage_ImportExport_Helper importExportHelper() importExportHelper()
 */
class Community2_Mage_ImportExport_Deleting_CustomerTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     * <p>Log in to Backend.</p>
     * <p>Navigate to System -> Export/p>
     */
    protected function assertPreConditions()
    {
        //logged in once for all tests
        $this->loginAdminUser();
        //Step 1
        $this->navigate('import');
    }
    /**
     * <p>Deleting Customer via Customers Main File</p>
     * <p>Preconditions:</p>
     * <p>1. Create two customers in Customers-> Manage Customers</p>
     * <p>2. Create .csv file with both customers: first with all attributes, second only with values of unique key</p>
     * <p>Steps</p>
     * <p>1. In System -> Import/ Export -> Import in drop-down "Entity Type" select "Customers"</p>
     * <p>2. Select "Delete Entities" in selector "Import Behavior"</p>
     * <p>3. Select "Magento 2.0 format"</p>
     * <p>4. Select "Customers Main File"</p>
     * <p>5. Choose file from precondition</p>
     * <p>6. Press "Check Data"</p>
     * <p>7. Press "Import" button</p>
     * <p>8. Open Customers-> Manage Customers</p>
     * <p>Expected: Verify that both customers are absent in the system</p>
     *
     * @test
     * @dataProvider importData
     * @TestlinkId TL-MAGE-5675
     */
    public function deletingCustomer($data)
    {
        //Create Customer1
        $this->navigate('manage_customers');
        $userData1 = $this->loadDataSet('Customers', 'generic_customer_account');
        $this->customerHelper()->createCustomer($userData1);
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Create Customer2
        $this->navigate('manage_customers');
        $userData2 = $this->loadDataSet('Customers', 'generic_customer_account');
        $this->customerHelper()->createCustomer($userData2);
        $this->assertMessagePresent('success', 'success_saved_customer');

        $data[0]['email'] = $userData1['email'];
        $data[0]['firstname'] = $userData1['first_name'];
        $data[0]['lastname'] = $userData1['last_name'];
        $data[0]['password'] = $userData1['password'];

        $data[1]['email'] = $userData2['email'];
        $data[1]['firstname'] = 'firstname_new';
        $data[1]['lastname'] = 'lastname_new';
        $data[1]['password'] = 'qqqqqqq';

        //Step 1
        $this->admin('import');
        $this->importExportHelper()->chooseImportOptions('Customers', 'Delete Entities',
            'Magento 2.0 format', 'Customers Main File');
        //Step 5, 6, 7
        $importReport = $this->importExportHelper()->import($data);
        //Check import
        $this->assertArrayHasKey('import', $importReport,
            'Import has been finished with issues: ' . print_r($importReport));
        $this->assertArrayHasKey('success', $importReport['import'],
            'Import has been finished with issues: ' . print_r($importReport));
        //Step 8
        $this->navigate('manage_customers');
        //Verify that the first customer is absent after import 'Delete Entities'
        $this->assertFalse($this->customerHelper()->isCustomerPresentInGrid($userData1), 'Customer is found');

        //Verify that the second customer is absent after import 'Delete Entities'
        $this->assertFalse($this->customerHelper()->isCustomerPresentInGrid($userData2), 'Customer is found');
    }

    public function importData()
    {
        return array(
            array(
                array(
                    $this->loadDataSet('ImportExport', 'generic_customer_csv'),
                    $this->loadDataSet('ImportExport', 'generic_customer_csv')
                )
            )
        );
    }
    /**
     * <p>Deleting Customer via Customers Main File</p>
     * <p>Preconditions:</p>
     * <p>1. Create two customers in Customers-> Manage Customers</p>
     * <p>2. Create .csv file with incorrect email for first customer, with incorrect website for second customer</p>
     * <p>Steps</p>
     * <p>1. In System -> Import/ Export -> Import in drop-down "Entity Type" select "Customers"</p>
     * <p>2. Select "Delete Entities" in selector "Import Behavior"</p>
     * <p>3. Select "Magento 2.0 format"</p>
     * <p>4. Select "Customers Main File"</p>
     * <p>5. Choose file from precondition</p>
     * <p>6. Press "Check Data"</p>
     * <p>8. Open Customers-> Manage Customers</p>
     * <p>Expected: Verify that both customers are present in the system</p>
     *
     * @test
     * @dataProvider importCustomerData
     * @TestlinkId TL-MAGE-5678
     */
    public function deletingCustomerWithDifferentEmailOrWebsite($data)
    {
        //Create Customer1
        $this->navigate('manage_customers');
        $userData1 = $this->loadDataSet('Customers', 'generic_customer_account');
        $this->customerHelper()->createCustomer($userData1);
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Create Customer2
        $this->navigate('manage_customers');
        $userData2 = $this->loadDataSet('Customers', 'generic_customer_account');
        $this->customerHelper()->createCustomer($userData2);
        $this->assertMessagePresent('success', 'success_saved_customer');

        $data[0]['email'] = 'not_existing_email@example.co';
        $data[0]['firstname'] = $userData1['first_name'];
        $data[0]['lastname'] = $userData1['last_name'];
        $data[0]['password'] = $userData1['password'];

        $data[1]['email'] = $userData2['email'];
        $data[1]['firstname'] = $userData2['first_name'];
        $data[1]['lastname'] = $userData2['last_name'];
        $data[1]['password'] = $userData2['password'];

        //Step 1, 2, 3, 4
        $this->admin('import');
        $this->importExportHelper()->chooseImportOptions('Customers', 'Delete Entities',
            'Magento 2.0 format', 'Customers Main File');
        //Step 5, 6, 7
        $importReport = $this->importExportHelper()->import($data);
        //Check import
        $this->assertArrayNotHasKey('import', $importReport,
            'Import has been finished with issues: ' . print_r($importReport));
        $this->assertArrayHasKey('error', $importReport['validation'],
            'Import has been finished with issues:');
        //Step 8
        $this->navigate('manage_customers');
        //Verify that the first customer is present after import 'Delete Entities'
        $this->assertTrue($this->customerHelper()->isCustomerPresentInGrid($userData1), 'Customer not found');
        //Verify that the second customer is present after import 'Delete Entities'
        $this->assertTrue($this->customerHelper()->isCustomerPresentInGrid($userData2), 'Customer not found');
    }

    public function importCustomerData()
    {
        return array(
            array(
                array(
                    $this->loadDataSet('ImportExport', 'generic_customer_csv'),
                    $this->loadDataSet('ImportExport', 'generic_customer_csv',
                        array('_website' => $this->generate('string', 30, ':digit:')))
                )
            )
        );
    }
}
