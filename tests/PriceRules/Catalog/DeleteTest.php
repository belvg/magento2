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
 * Catalog Price Rule Delete
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceRules_Catalog_DeleteTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Login to backend</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to Promotions -> Catalog Price Rules</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_catalog_price_rules');
        $this->assertTrue($this->checkCurrentPage('manage_catalog_price_rules'), $this->messages);
    }

    /**
     * <p>Delete Catalog Price Rule</p>
     * <p>PreConditions</p>
     * <p>New Catalog Price rule created</p>
     * <p>Steps</p>
     * <p>1. Open created Rule</p>
     * <p>2. Click "Delete Rule" button</p>
     * <p>3. Click "Ok" in confirmation window</p>
     * <p>4. Check confirmation message</p>
     *
     * <p>Expected result: Success message appears, rule removed from the list</p>
     *
     * @test
     */
    public function deleteCatalogPriceRule()
    {
        //Data
        $priceRuleData = $this->loadData('test_catalog_rule', array('customer_groups' => 'General'), 'rule_name');
        $this->addParameter('id', $this->defineIdFromUrl());
        $this->addParameter('elementTitle', $priceRuleData['info']['rule_name']);
        $ruleSearch = $this->loadData('search_catalog_rule',
                 array('filter_rule_name'   => $priceRuleData['info']['rule_name']));
        //PreConditions
        $this->priceRulesHelper()->createRule($priceRuleData);
        //Verification
        $this->assertTrue($this->successMessage('success_saved_rule'), $this->messages);
        $this->assertTrue($this->successMessage('notification_message'), $this->messages);
        //Steps
        $this->priceRulesHelper()->openRule($ruleSearch);
        $this->clickButtonAndConfirm('delete_rule', 'confirmation_for_delete');
        $this->assertTrue($this->successMessage('success_deleted_rule', $this->messages));
        //Verification
        $this->assertEquals(NULL, $this->search(array('rule_name' => $priceRuleData['info']['rule_name'])));
    }

}
