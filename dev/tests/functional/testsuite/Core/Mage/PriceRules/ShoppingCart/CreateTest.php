<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_PriceRules
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Creating rules with correct and incorrect data without applying them
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_PriceRules_ShoppingCart_CreateTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     * <p>Login Admin user to backend</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Bug MAGE-5623 (reproducible in 1.6.2, but is not reproducible in nightly build)</p>
     * <p>Create Shopping cart price rule with empty required fields.</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Promotions - Shopping Cart Price Rules;</p>
     * <p>2. Fill form for Shopping Cart Price Rules,
     *       but leave one required field empty (empty fields are listed in data provider);</p>
     * <p>3. Try to save newly created Shopping Cart Price Rules with one empty required field;</p>
     * <p>Expected results:</p>
     * <p>Rule is not created; Message "This is required field" is shown under each empty required field;</p>
     *
     * @param string $fieldName
     * @param string $fieldType
     *
     * @test
     * @dataProvider withRequiredFieldsEmptyDataProvider
     * @TestlinkId TL-MAGE-3316
     * @group skip_due_to_bug
     */
    public function withRequiredFieldsEmpty($fieldName, $fieldType)
    {
        $this->navigate('manage_shopping_cart_price_rules');
        $dataToOverride = array();
        if ($fieldType == 'multiselect') {
            $dataToOverride[$fieldName] = '%noValue%';
        } else {
            $dataToOverride[$fieldName] = '';
        }
        $ruleData = $this->loadDataSet('ShoppingCartPriceRule', 'scpr_required_fields', $dataToOverride);
        $this->priceRulesHelper()->createRule($ruleData);
        $this->addFieldIdToMessage($fieldType, $fieldName);
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withRequiredFieldsEmptyDataProvider()
    {
        return array(
            array('rule_name', 'field'),
            array('customer_groups', 'multiselect'),
            array('coupon_code', 'field'),
            array('discount_amount', 'field')
        );
    }

    /**
     * <p>Create Shopping cart price rule with special symbols in fields.</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Promotions - Shopping Cart Price Rules;</p>
     * <p>2. Fill form for Shopping Cart Price Rules,
     *       but one field should be filled with special symbols (fields are listed in data provider);</p>
     * <p>3. Try to save newly created Shopping Cart Price Rules with one field filled with special symbols;</p>
     * <p>Expected results:</p>
     * <p>Rule is created where applicable;</p>
     *
     * @test
     * @TestlinkId TL-MAGE-3320
     * @group skip_due_to_bug
     */
    public function createWithRequiredFieldsWithSpecialSymbols()
    {
        $this->navigate('manage_shopping_cart_price_rules');
        $ruleData = $this->loadDataSet('ShoppingCartPriceRule', 'scpr_required_fields',
            array('rule_name'   => $this->generate('string', 32, ':punct:'),
                  'coupon_code' => $this->generate('string', 32, ':punct:')));
        $ruleSearch = $this->loadDataSet('ShoppingCartPriceRule', 'search_shopping_cart_rule',
            array('filter_rule_name'   => $ruleData['info']['rule_name'],
                  'filter_coupon_code' => $ruleData['info']['coupon_code']));
        $this->priceRulesHelper()->createRule($ruleData);
        $this->assertMessagePresent('success', 'success_saved_rule');
        $this->priceRulesHelper()->openRule($ruleSearch);
        $this->priceRulesHelper()->verifyRuleData($ruleData);
    }

    /**
     * <p>Create Shopping cart price rule with required fields only filled.</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Promotions - Shopping Cart Price Rules;</p>
     * <p>2. Fill form (only required fields) for Shopping Cart Price Rules;</p>
     * <p>3. Save newly created Shopping Cart Price Rules;</p>
     * <p>Expected results:</p>
     * <p>Rule is created;</p>
     *
     * @return string   Returns coupon code
     * @test
     * @TestlinkId TL-MAGE-3319
     */
    public function createWithRequiredFields()
    {
        $this->navigate('manage_shopping_cart_price_rules');
        $ruleData = $this->loadDataSet('ShoppingCartPriceRule', 'scpr_required_fields');
        $this->priceRulesHelper()->createRule($ruleData);
        $this->assertMessagePresent('success', 'success_saved_rule');

        return $ruleData['info']['coupon_code'];
    }

    /**
     * <p>Create Shopping cart price rule with all fields filled (except conditions).</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Promotions - Shopping Cart Price Rules;</p>
     * <p>2. Fill form (all fields) for Shopping Cart Price Rules;</p>
     * <p>3. Save newly created Shopping Cart Price Rules;</p>
     * <p>Expected results:</p>
     * <p>Rule is created;</p>
     *
     * @test
     * @TestlinkId TL-MAGE-3315
     */
    public function createWithAllFields()
    {
        $this->navigate('manage_shopping_cart_price_rules');
        $ruleData = $this->loadDataSet('ShoppingCartPriceRule', 'scpr_all_fields');
        $this->priceRulesHelper()->createRule($ruleData);
        $this->assertMessagePresent('success', 'success_saved_rule');
    }

    /**
     * <p>Create Shopping cart price rule with all fields filled (except conditions).</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Promotions - Shopping Cart Price Rules;</p>
     * <p>2. Fill form (all fields) for Shopping cart price rule;</p>
     * <p>3. Save newly created Shopping Cart Price Rule;</p>
     * <p>Expected results:</p>
     * <p>Rule is created;</p>
     *
     * @test
     * @TestlinkId    TL-MAGE-3318
     */
    public function createWithoutCoupon()
    {
        $this->navigate('manage_shopping_cart_price_rules');
        $ruleData = $this->loadDataSet('ShoppingCartPriceRule', 'scpr_all_fields',
            array('coupon'          => 'No Coupon',
                  'coupon_code'     => '%noValue%',
                  'uses_per_coupon' => '%noValue%'));
        $this->priceRulesHelper()->createRule($ruleData);
        $this->assertMessagePresent('success', 'success_saved_rule');
    }

    /**
     * <p>Create Shopping cart price rule with existing coupon.</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Promotions - Shopping Cart Price Rules;</p>
     * <p>2. Fill form (all fields) for Shopping Cart Price Rules;</p>
     * <p>3. Save newly created Shopping Cart Price Rules;</p>
     * <p>Expected results:</p>
     * <p>Rule is created;</p>
     *
     * <p>4. Create rule with the same coupon;</p>
     * <p>Expected Results:</p>
     * <p>Rule is not created; Message "Coupon with the same code already exists." appears.</p>
     *
     * @param string $coupon Code
     *
     * @test
     * @depends createWithRequiredFields
     * @TestlinkId TL-MAGE-3317
     */
    public function createWithExistingCoupon($coupon)
    {
        $this->navigate('manage_shopping_cart_price_rules');
        $ruleData = $this->loadDataSet('ShoppingCartPriceRule', 'scpr_all_fields', array('coupon_code' => $coupon));
        $this->priceRulesHelper()->createRule($ruleData);
        $this->assertMessagePresent('error', 'error_coupon_code_exists');
    }
}