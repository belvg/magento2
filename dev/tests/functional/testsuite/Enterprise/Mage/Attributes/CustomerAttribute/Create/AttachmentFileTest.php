<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Attributes
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**`
 * Create new customer attribute. Type: File (attachment)
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Enterprise_Mage_Attributes_CustomerAttribute_Create_AttachmentFileTest extends Mage_Selenium_TestCase
{
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('manage_customer_attributes');
    }

    /**
     * @test
     */
    public function navigation()
    {
        $this->assertTrue($this->buttonIsPresent('add_new_attribute'),
            'There is no "Add New Attribute" button on the page');
        $this->clickButton('add_new_attribute');
        $this->assertTrue($this->checkCurrentPage('new_customer_attribute'), $this->getParsedMessages());
        $this->assertTrue($this->buttonIsPresent('back'), 'There is no "Back" button on the page');
        $this->assertTrue($this->buttonIsPresent('reset'), 'There is no "Reset" button on the page');
        $this->assertTrue($this->buttonIsPresent('save_attribute'), 'There is no "Save" button on the page');
        $this->assertTrue($this->buttonIsPresent('save_and_continue_edit'),
            'There is no "Save and Continue Edit" button on the page');
    }

    /**
     * <p>Create "File (attachment)" type Customer Attributes (required fields only)</p>
     *
     * @return array
     * @test
     * @depends navigation
     * @TestlinkId TL-MAGE-5529
     */
    public function withRequiredFieldsOnly()
    {
        //Data
        $attrData = $this->loadDataSet('CustomerAttribute', 'customer_attribute_attach_file');
        //Steps
        $this->attributesHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');
        return $attrData;
    }

    /**
     * <p>Checking  of verification for duplicate of Customer Attributes with similar code
     * Creation of new attribute with existing code.</p>
     *
     * @param array $attrData
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-5530
     */
    public function withAttributeCodeThatAlreadyExists(array $attrData)
    {
        //Steps
        $this->attributesHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('error', 'exists_attribute_code');
    }

    /**
     * <p>Checking validation  for required fields are EMPTY</p>
     *
     * @param $emptyField
     * @param $messageCount
     *
     * @test
     * @dataProvider withRequiredFieldsEmptyDataProvider
     * @TestlinkId TL-MAGE-5531
     */
    public function withRequiredFieldsEmpty($emptyField, $messageCount)
    {
        //Data
        $attrData = $this->loadDataSet('CustomerAttribute', 'customer_attribute_attach_file',
            array($emptyField => '%noValue%'));
        //Steps
        $this->attributesHelper()->createAttribute($attrData);
        //Verifying
        $this->addFieldIdToMessage('field', $emptyField);
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount($messageCount), $this->getParsedMessages());
    }

    public function withRequiredFieldsEmptyDataProvider()
    {
        return array(
            array('attribute_code', 1),
            array('sort_order', 1),
            array('attribute_label', 1)
        );
    }

    /**
     * <p>Checking validation for valid data in the 'Attribute Code' field</p>
     *
     * @param $wrongAttributeCode
     * @param $validationMessage
     *
     * @test
     * @dataProvider withInvalidAttributeCodeDataProvider
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-5532
     */
    public function withInvalidAttributeCode($wrongAttributeCode, $validationMessage)
    {
        //Data
        $attrData = $this->loadDataSet('CustomerAttribute', 'customer_attribute_attach_file',
            array('attribute_code' => $wrongAttributeCode));
        //Steps
        $this->attributesHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('validation', $validationMessage);
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withInvalidAttributeCodeDataProvider()
    {
        return array(
            array('11code_wrong', 'invalid_attribute_code'),
            array('CODE_wrong', 'invalid_attribute_code'),
            array('wrong code', 'invalid_attribute_code'),
            array($this->generate('string', 11, ':punct:'), 'invalid_attribute_code'),
            array($this->generate('string', 33, ':lower:'), 'wrong_length_attribute_code')
        );
    }

    /**
     * <p>Checking validation for field  field Maximum File Size</p>
     *
     * @param $wrongAttributeCode
     * @param $validationMessage
     *
     * @test
     * @dataProvider withInvalidFileSizeDataProvider
     * @TestlinkId TL-MAGE-5534
     */
    public function withInvalidFileSize($wrongAttributeCode, $validationMessage)
    {
        //Data
        $attrData = $this->loadDataSet('CustomerAttribute', 'customer_attribute_attach_file',
            array('maximum_file_size' => $wrongAttributeCode));
        //Steps
        $this->attributesHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('validation', $validationMessage);
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withInvalidFileSizeDataProvider()
    {
        return array(
            array('testdasdsa', 'use_numbers_only'),
            array($this->generate('string', 11, ':punct:'), 'use_numbers_only'),
            array($this->generate('string', 33, ':lower:'), 'use_numbers_only')
        );
    }

    /**
     * <p>Checking of correct validate of submitting form by using special
     * characters for all fields exclude 'Attribute Code' field.</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-5535
     */
    public function withSpecialCharactersInTitle()
    {
        //Data
        $attrData = $this->loadDataSet('CustomerAttribute', 'customer_attribute_attach_file',
            array('attribute_label' => $this->generate('string', 32, ':punct:')));
        $attrData['properties']['attribute_label'] = preg_replace('/<|>/', '',
        $attrData['properties']['attribute_label']);
        $searchData = $this->loadDataSet('CustomerAttribute', 'customer_attribute_search_data',
            array('attribute_code' => $attrData['properties']['attribute_code']));
        //Steps
        $this->attributesHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');
        //Steps
        $this->attributesHelper()->openAttribute($searchData);
        //Verifying
        $this->attributesHelper()->verifyAttribute($attrData);
    }

    /**
     * <p>Checking of correct work of submitting form by using long values for fields filling</p>
     *
     * @test
     * @depends withRequiredFieldsOnly
     * @TestlinkId TL-MAGE-5536
     */
    public function withLongValues()
    {
        //Data
        $attrData = $this->loadDataSet('CustomerAttribute', 'customer_attribute_attach_file',
            array('attribute_code'  => $this->generate('string', 21, ':lower:'),
                  'attribute_label' => $this->generate('string', 255, ':alnum:')));
        $searchData = $this->loadDataSet('CustomerAttribute', 'customer_attribute_search_data',
            array('attribute_code'  => $attrData['properties']['attribute_code'],
                  'attribute_label' => $attrData['properties']['attribute_label']));
        //Steps
        $this->attributesHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');
        //Steps
        $this->attributesHelper()->openAttribute($searchData);
        //Verifying
        $this->attributesHelper()->verifyAttribute($attrData);
    }
}