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
 * Create new product attribute. Type: Text Field
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttribute_Create_TextFieldTest extends Mage_Selenium_TestCase {

    /**
     * Preconditions:
     * Admin user should be logged in.
     * Should stays on the Admin Dashboard page after login.
     * Navigate to System -> Manage Attributes.
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->assertTrue($this->checkCurrentPage('dashboard'),
                'Wrong page is opened');
        $this->navigate('manage_attributes');
        $this->assertTrue($this->checkCurrentPage('manage_attributes'),
                'Wrong page is opened');
    }

    public function test_Navigation()
    {
        $this->assertTrue($this->clickButton('add_new_attribute'),
                'There is no "Add New Attribute" button on the page');
        $this->assertTrue($this->checkCurrentPage('new_product_attribute'),
                'Wrong page is opened');
        $this->assertTrue($this->controlIsPresent('button', 'back'),
                'There is no "Back" button on the page');
        $this->assertTrue($this->controlIsPresent('button', 'reset'),
                'There is no "Reset" button on the page');
        $this->assertTrue($this->controlIsPresent('button', 'save_attribute'),
                'There is no "Save" button on the page');
        $this->assertTrue($this->controlIsPresent('button', 'save_and_continue_edit'),
                'There is no "Save and Continue Edit" button on the page');
    }

    /**
     * Create "Text Field" type Product Attribute (required fields only)
     *
     * Steps:
     * 1.Click on "Add New Attribute" button
     * 2.Choose "Text Field" in 'Catalog Input Type for Store Owner' dropdown
     * 3.Fill all required fields
     * 4.Click on "Save Attribute" button
     *
     * Expected result:
     * New attribute ["Text Field" type] successfully created.
     * Success message: 'The product attribute has been saved.' is displayed.
     *
     * @depends test_Navigation
     */
    public function test_WithRequiredFieldsOnly()
    {
        //Data
        $attrData = $this->loadData('product_attribute_textfield', null, array('attribute_code', 'admin_title'));
        //Steps
        $this->createAttribute($attrData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_attribute'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_attributes'),
                'After successful customer creation should be redirected to Manage Attributes page');

        return $attrData;
    }

    /**
     * Checking of verification for duplicate of Product Attributes with similar code
     * Creation of new attribute with existing code.
     *
     * Steps:
     * 1.Click on "Add New Attribute" button
     * 2.Choose "Text Field" in 'Catalog Input Type for Store Owner' dropdown
     * 3.Fill 'Attribute Code' field by code used in test before.
     * 4.Fill other required fields by regular data.
     * 5.Click on "Save Attribute" button
     *
     * Expected result:
     * New attribute ["Text Field" type] shouldn't be created.
     * Error message: 'Attribute with the same code already exists' is displayed.
     *
     * @depends test_WithRequiredFieldsOnly
     */
    public function test_WithAttributeCodeThatAlreadyExists(array $attrData)
    {
        //Steps
        $this->createAttribute($attrData);
        //Verifying
        $this->assertTrue($this->errorMessage('exists_attribute_code'), $this->messages);
    }

    /**
     * Checking validation for required fields are EMPTY
     *
     * Steps:
     * 1.Click on "Add New Attribute" button
     * 2.Choose "Text Field" in 'Catalog Input Type for Store Owner' dropdown
     * 3.Skip filling of one field required and fill other required fields.
     * 4.Click on "Save Attribute" button
     *
     * Expected result:
     * New attribute ["Text Field" type] shouldn't be created.
     * Error JS message: 'This is a required field.' is displayed.
     *
     * @dataProvider data_EmptyField
     * @depends test_WithRequiredFieldsOnly
     */
    public function test_WithRequiredFieldsEmpty($emptyField)
    {
        //Data
        $attrData = $this->loadData('product_attribute_textfield', $emptyField);
        //Steps
        $this->createAttribute($attrData);
        //Verifying
        $page = $this->getUimapPage('admin', 'new_product_attribute');
        foreach ($emptyField as $fieldName => $fieldXpath) {
            switch ($fieldName) {
                case 'attribute_code':
                    $fieldSet = $page->findFieldset('attribute_properties');
                    $xpath = $fieldSet->findField($fieldName);
                    break;
                case 'admin_title':
                    $fieldSet = $page->findFieldset('manage_titles');
                    $xpath = $fieldSet->findField($fieldName);
                    break;
                case 'apply_to':
                    $fieldSet = $page->findFieldset('attribute_properties');
                    $xpath = $fieldSet->findMultiselect('apply_product_types');
                    break;
            }
            $this->addParameter('fieldXpath', $xpath);
        }
        $this->assertTrue($this->errorMessage('empty_required_field'), $this->messages);
        $this->assertTrue($this->verifyMessagesCount(), $this->messages);
    }

    public function data_EmptyField()
    {
        return array(
            array(array('attribute_code' => '')),
            array(array('admin_title' => '')),
            array(array('apply_to' => 'Selected Product Types')),
        );
    }

    /**
     * Checking validation for valid data in the 'Attribute Code' field
     *
     * Steps:
     * 1.Click on "Add New Attribute" button
     * 2.Choose "Text Field" in 'Catalog Input Type for Store Owner' dropdown
     * 3.Fill 'Attribute Code' field by invalid data [Examples: '0xxx'/'_xxx'/'111']
     * 4.Fill other required fields by regular data.
     * 5.Click on "Save Attribute" button
     *
     * Expected result:
     * New attribute ["Text Field" type] shouldn't be created.
     * Error JS message: 'Please use only letters (a-z), numbers (0-9) or underscore(_) in
     * this field, first character should be a letter.' is displayed.
     *
     * @dataProvider data_WrongCode
     * @depends test_WithRequiredFieldsOnly
     */
    public function test_WithInvalidAttributeCode($wrongAttributeCode)
    {
        //Data
        $attrData = $this->loadData('product_attribute_textfield', $wrongAttributeCode);
        //Steps
        $this->createAttribute($attrData);
        //Verifying
        $this->assertTrue($this->errorMessage('invalid_attribute_code'), $this->messages);
    }

    public function data_WrongCode()
    {
        return array(
            array(array('attribute_code' => '11code_wrong')),
            array(array('attribute_code' => 'CODE_wrong')),
            array(array('attribute_code' => 'wrong code')),
            array(array('attribute_code' => $this->generate('string', 11, ':punct:'))),
        );
    }

    /**
     * Checking of correct validate of submitting form by using special
     * characters for all fields exclude 'Attribute Code' field.
     *
     * Steps:
     * 1.Click on "Add New Attribute" button
     * 2.Choose "Text Field" in 'Catalog Input Type for Store Owner' dropdown
     * 3.Fill 'Attribute Code' field by regular data.
     * 4.Fill other required fields by special characters.
     * 5.Click on "Save Attribute" button
     *
     * Expected result:
     * New attribute ["Text Field" type] successfully created.
     * Success message: 'The product attribute has been saved.' is displayed.
     *
     * @depends test_WithRequiredFieldsOnly
     */
    public function test_WithSpecialCharacters_InTitle()
    {
        //Data
        $attrData = $this->loadData('product_attribute_textfield',
                        array('admin_title' => $this->generate('string', 32, ':punct:')), 'attribute_code');
        //Steps
        $this->createAttribute($attrData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_attribute'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_attributes'),
                'After successful customer creation should be redirected to Manage Attributes page');
    }

    /**
     * Checking of correct work of submitting form by using long values for fields filling
     *
     * Steps:
     * 1.Click on "Add New Attribute" button
     * 2.Choose "Text Field" in 'Catalog Input Type for Store Owner' dropdown
     * 3.Fill all required fields by long value alpha-numeric data.
     * 4.Click on "Save Attribute" button
     *
     * Expected result:
     * New attribute ["Text Field" type] successfully created.
     * Success message: 'The product attribute has been saved.' is displayed.
     *
     * @depends test_WithRequiredFieldsOnly
     */
    public function test_WithLongValues()
    {
        //Data
        $attrData = $this->loadData('product_attribute_textfield',
                        array(
                            'attribute_code' => $this->generate('string', 255, ':lower:'),
                            'admin_title' => $this->generate('string', 255, ':alnum:'),
                ));
        $searchData = $this->loadData('attribute_search_data',
                        array(
                            'attribute_code' => $attrData['attribute_code'],
                            'attribute_lable' => $attrData['admin_title'],
                ));
        //Steps
        $this->createAttribute($attrData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_attribute'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_attributes'),
                'After successful customer creation should be redirected to Manage Attributes page');
        //Steps
        $this->clickButton('reset_filter');
        $this->navigate('manage_attributes');
        $this->assertTrue($this->searchAndOpen($searchData), 'Attribute is not found');
        //Verifying
        $this->assertTrue($this->verifyForm($attrData, 'properties'), $this->messages);
        $this->clickControl('tab', 'manage_lables_options', FALSE);
        $this->assertTrue($this->verifyForm($attrData, 'manage_lables_options'), $this->messages);
        $this->manageLabelsAndOptionsForStoreView($attrData, 'verify');
    }

    /**
     * Checking of attributes creation functionality during product createion process
     *
     * Steps:
     * 1.Go to Catalog->Attributes->Manage Products
     * 2.Click on "Add Product" button
     * 3.Specify settings for product creation
     * 3.1.Select "Attribute Set"
     * 3.2.Select "Product Type"
     * 4.Click on "Continue" button
     * 5.Click on "Create New Attribute" button in the top of "General" fieldset under "General" tab
     * 6.Choose "Text Field" in 'Catalog Input Type for Store Owner' dropdown
     * 7.Fill all required fields.
     * 8.Click on "Save Attribute" button
     *
     * Expected result:
     * New attribute ["Text Field" type] successfully created.
     * Success message: 'The product attribute has been saved.' is displayed.
     * Pop-up window is closed automatically
     *
     * @depends test_WithRequiredFieldsOnly
     */
    public function test_OnProductPage_WithRequiredFieldsOnly()
    {
        //Data
        $productSettings = $this->loadData('product_create_settings_virtual');
        $attrData = $this->loadData('product_attribute_textfield',
                        null, array('attribute_code', 'admin_title'));
        // Defining and adding %attributeId% for Uimap pages.
        $this->addParameter('attributeId', 0);
        //Steps. Open 'Manage Products' page, click 'Add New Product' button, fill in form.
        $this->navigate('manage_products');
        $this->clickButton('add_new_product');
        $this->assertTrue($this->checkCurrentPage('new_product_settings'),
                'Wrong page is displayed'
        );
        $this->fillForm($productSettings);
        // Defining and adding %attributeSetID% and %productType% for Uimap pages.
        $page = $this->getCurrentUimapPage();
        $fieldSet = $page->findFieldset('product_settings');
        foreach ($productSettings as $fieldsName => $fieldValue) {
            $xpath = '//' . $fieldSet->findDropdown($fieldsName);
            switch ($fieldsName) {
                case 'attribute_set':
                    $attributeSetID = $this->getValue($xpath . "/option[text()='$fieldValue']");
                    break;
                case 'product_type':
                    $productType = $this->getValue($xpath . "/option[text()='$fieldValue']");
                    break;
                default:
                    break;
            }
        }
        $this->addParameter('attributeSetID', $attributeSetID);
        $this->addParameter('productType', $productType);
        //Steps. Сlick 'Сontinue' button
        $this->clickButton('continue_button');
        // Defining and adding %fieldSetId% for Uimap pages.
        $page = $this->getCurrentUimapPage();
        $fieldSet = $page->findFieldset('general');
        $id = explode('_', $this->getAttribute('//' . $fieldSet->getXPath() . '@id'));
        foreach ($id as $value) {
            if (is_numeric($value)) {
                $fieldSetId = $value;

                $this->addParameter('fieldSetId', $fieldSetId);
                break;
            }
        }
        //Steps. Сlick 'Create New Attribute' button, select opened window.
        $this->clickButton('create_new_attribute', FALSE);
        $names = $this->getAllWindowNames();
        $this->waitForPopUp(end($names), '30000');
        $this->selectWindow("name=" . end($names));
        //Steps. Fill in forms and save.
        $this->fillForm($attrData, 'properties');
        $this->clickControl('tab', 'manage_lables_options', false);
        $this->fillForm($attrData, 'manage_lables_options');
        $this->manageLabelsAndOptionsForStoreView($attrData);
        $this->saveForm('save_attribute');
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_attribute'), $this->messages);
        $this->selectWindow(NULL);
    }

    /**
     * *********************************************
     * *         HELPER FUNCTIONS                  *
     * *********************************************
     */

    /**
     * Action_helper method for Create Attribute action
     *
     * @param array $attrData Array which contains DataSet for filling of the current form
     */
    public function createAttribute($attrData)
    {
        $this->clickButton('add_new_attribute');
        $this->fillForm($attrData, 'properties');
        $this->clickControl('tab', 'manage_lables_options', false);
        $this->fillForm($attrData, 'manage_lables_options');
        $this->manageLabelsAndOptionsForStoreView($attrData);
        $this->manageAttributeOptions($attrData);
        $this->saveForm('save_attribute');
    }

    /**
     * Fill in(or verify) 'Title' field for different Store Views.
     *
     * PreConditions: attribute page is opened on 'Manage Label / Options' tab.
     *
     * @param array $attrData
     * @param string $action
     */
    public function manageLabelsAndOptionsForStoreView($attrData, $action = 'fill', $type ='titles')
    {
        $page = $this->getCurrentLocationUimapPage();
        $dataArr = array();
        switch ($type) {
            case 'titles':
                $fieldSet = $page->findFieldset('manage_titles');
                foreach ($attrData as $f_key => $d_value) {
                    if (preg_match('/title/', $f_key) and is_array($attrData[$f_key])) {
                        reset($attrData[$f_key]);
                        $key = current($attrData[$f_key]);
                        $value = next($attrData[$f_key]);
                        $dataArr[$key] = $value;
                    }
                }
                break;
            case 'options':
                $fieldSet = $page->findFieldset('manage_options');
                foreach ($attrData as $f_key => $d_value) {
                    if (preg_match('/option/', $f_key) and is_array($attrData[$f_key])) {
                        foreach ($attrData[$f_key] as $k1 => $v2) {
                            if (is_array($attrData[$f_key][$k1]) and preg_match('/store_view_option_name/', $k1)) {
                                reset($attrData[$f_key][$k1]);
                                $key = current($attrData[$f_key][$k1]);
                                $value = next($attrData[$f_key][$k1]);
                                $dataArr[$key] = $value;
                            }
                        }
                    }
                }
                break;
        }
        $xpath = '//' . $fieldSet->getXPath();
        $qtyStore = $this->getXpathCount($xpath . '//th');
        foreach ($dataArr as $k => $v) {
            $number = -1;
            for ($i = 1; $i <= $qtyStore; $i++) {
                if ($this->getText($xpath . "//th[$i]") == $k) {
                    $number = $i;
                    break;
                }
            }
            if ($number != -1) {
                switch ($type) {
                    case 'titles':
                        $this->addParameter('fieldTitleNumber', $number);
                        $fieldName = 'title_by_store_name';
                        break;
                    case 'options':
                        $this->addParameter('storeViewID', $number);
                        $fieldName = 'option_name_by_store_name';
                        break;
                }

                $page->assignParams($this->_paramsHelper);
                switch ($action) {
                    case 'fill':
                        $this->type($xpath . '//' . $page->findField($fieldName), $v);
                        break;
                    case 'verify':
                        $this->assertEquals($this->getValue($xpath . '//' . $page->findField($fieldName)),
                                $v, 'Stored data not equals to specified');
                        break;
                }
            } else {
                throw new OutOfRangeException("Can't find specified store view.");
            }
        }
    }

    public function manageAttributeOptions($attrData, $action = 'fill')
    {
        $page = $this->getCurrentLocationUimapPage();
        $fieldSet = $page->findFieldset('manage_options');
        $fieldSetXpath = '//' . $fieldSet->getXPath();
        foreach ($attrData as $key => $value) {
            if (preg_match('/option/', $key) and is_array($attrData[$key])) {
                if ($this->isElementPresent($fieldSetXpath)) {
                    $optionCount = $this->getXpathCount($fieldSetXpath . "//tr[contains(@class,'option-row')]");
                    $this->addParameter('fieldOptionNumber', $optionCount);
                    $page->assignParams($this->_paramsHelper);
                    $this->clickButton('add_option', FALSE);
                    $this->fillForm($attrData[$key], 'manage_lables_options');
                    $this->manageLabelsAndOptionsForStoreView($attrData, $action, 'options');
                }
            }
        }
    }

}
