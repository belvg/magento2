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
 * Delete Customer  Attributes
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Community17_Mage_CustomerAttribute_DeleteTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     * <p>Navigate to Customers -> Attributes -> Manage Customer  Attributes</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('manage_customer_attributes');
    }

    /**
     * <p>Delete Customer Attributes</p>
     * <p>Steps:</p>
     * <p>1.Click on "Add New Attribute" button</p>
     * <p>2.Fill all required fields</p>
     * <p>3.Click on "Save Attribute" button</p>
     * <p>4.Search and open attribute</p>
     * <p>5.Click on "Delete Attribute" button</p>
     * <p>Expected result:</p>
     * <p>Attribute successfully deleted.</p>
     * <p>Success message: 'The customer attribute has been deleted.' is displayed.</p>
     *
     * @param $dataName
     *
     * @test
     * @dataProvider deleteProductAttributeDeletableDataProvider
     * @TestlinkId TL-MAGE-5596
     */
    public function deleteProductAttributeDeletable($dataName)
    {
        //Data
        $attrData = $this->loadDataSet('CustomerAttribute', $dataName);
        $searchData = $this->loadDataSet('CustomerAttribute', 'attribute_search_data',
            array('attribute_code' => $attrData['properties']['attribute_code']));
        //Steps
        $this->customerAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');
        //Steps
        $this->customerAttributeHelper()->openAttribute($searchData);
        $this->clickButtonAndConfirm('delete_attribute', 'delete_confirm_message');
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_attribute');
    }

    public function deleteProductAttributeDeletableDataProvider()
    {
        return array(
            array('customer_attribute_textfield'),
            array('customer_attribute_textarea'),
            array('customer_attribute_multipleline'),
            array('customer_attribute_date'),
            array('customer_attribute_dropdown'),
            array('customer_attribute_multiselect'),
            array('customer_attribute_yesno'),
            array('customer_attribute_attach_file'),
            array('customer_attribute_image_file')
        );
    }

    /**
     * <p>Delete system  Customer Attributes</p>
     * <p>Steps:</p>
     * <p>1.Search and open system Customer Attributes.</p+>
     * <p>Expected result:</p>
     * <p>"Delete Attribute" button isn't present.</p>
     *
     * @param array $attributeName
     *
     * @test
     * @dataProvider deleteSystemAttributeDataProvider
     * @TestlinkId TL-MAGE-5597
     */
    public function deletedSystemAttribute($attributeName)
    {
        //Data
        $searchData = $this->loadDataSet('CustomerAttribute', 'attribute_search_data',
            array('attribute_code'  => $attributeName));
        //Steps
        $this->customerAttributeHelper()->openAttribute($searchData);
        //Verifying
        $this->assertFalse($this->buttonIsPresent('delete_attribute'),
            '"Delete Attribute" button is present on the page');
    }

    public function deleteSystemAttributeDataProvider()
    {
        return array(
            array('created_at'),
            array('reward_update_notification'),
            array('reward_warning_notification'),
            array('website_id'),
            array('created_in'),
            array('group_id'),
            array('prefix'),
            array('firstname'),
            array('middlename'),
            array('lastname'),
            array('suffix'),
            array('email'),
            array('dob'),
            array('taxvat'),
            array('gender'),
        );
    }
}
