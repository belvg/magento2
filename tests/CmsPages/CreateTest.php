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
 * Create Cms Page Test
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CmsPages_CreateTest extends Mage_Selenium_TestCase
{

    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
        $this->navigate('manage_stores');
        $this->storeHelper()->createStore('generic_store_view', 'store_view');
        $this->assertMessagePresent('success', 'success_saved_store_view');
    }

    protected function assertPreconditions()
    {
        $this->loginAdminUser();
        $this->addParameter('id', '0');
    }

    /**
     * <p>Preconditions</p>
     * <p>Creates Category to use during tests</p>
     *
     * @test
     */
    public function preconditionsForTests()
    {
        //Data
        $category = $this->loadData('sub_category_required');
        $product = $this->loadData('simple_product_for_order',
                array('categories' => $category['parent_category'] . '/' . $category['name']));
        //Steps
        $this->navigate('manage_categories');
        $this->categoryHelper()->createCategory($category);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_category');
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($product);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');

        return array(
            'category_path' => $product['categories'],
            'filter_sku' => $product['general_sku'],
        );
    }

    /**
     * <p>Creates Page with required fields</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Pages page</p>
     * <p>2. Create page with required fields</p>
     * <p>Expected result</p>
     * <p>Page is created successfully</p>
     *
     * @test
     */
    public function withRequiredFields()
    {
        //Data
        $pageData = $this->loadData('new_cms_page_req');
        //Steps
        $this->navigate('manage_cms_pages');
        $this->cmsPagesHelper()->createCmsPage($pageData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_cms_page');
        $this->cmsPagesHelper()->frontValidatePage($pageData);

        return $pageData;
    }

    /**
     * <p>Creates Page with all fields and all types of widgets</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Pages page</p>
     * <p>2. Create page with all fields filled and all types of widgets</p>
     * <p>Expected result</p>
     * <p>Page is created successfully</p>
     *
     * @depends preconditionsForTests
     * @test
     */
    public function withAllFields($data)
    {
        //Data
        $pageData = $this->loadData('new_page_all_fields', $data);
        //Steps
        $this->navigate('manage_cms_pages');
        $this->cmsPagesHelper()->createCmsPage($pageData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_cms_page');
        $this->cmsPagesHelper()->frontValidatePage($pageData);
    }

    /**
     * <p>Creates Page with all fields filled except one empty</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Pages page</p>
     * <p>2. Create page with all fields filled, but leave one empty</p>
     * <p>Expected result</p>
     * <p>Page is not created successfully</p>
     *
     * @dataProvider dataEmptyAllFields
     * @depends withRequiredFields
     * @test
     */
    public function withEmptyRequiredFields($fieldName, $fielsType, $messCount)
    {
        //Data
        $pageData = $this->loadData('new_cms_page_req', array($fieldName => '%noValue%'));
        if ($fieldName == 'widget_type') {
            $this->overrideData('widget_1', array($fieldName => '-- Please Select --'), $pageData);
        }

        //Steps
        $this->navigate('manage_cms_pages');
        $this->cmsPagesHelper()->createCmsPage($pageData);
        //Verification
        if ($fieldName == 'content') {
            $fieldName = 'editor_disabled';
        }
        if ($fieldName == 'filter_url_key') {
            $fieldName = 'chosen_option';
        }
        $this->addFieldIdToMessage($fielsType, $fieldName);
        $this->assertTrue($this->verifyMessagesCount($messCount), $this->getParsedMessages());
        $this->assertMessagePresent('validation', 'empty_required_field');
    }

    public function dataEmptyAllFields()
    {
        return array(
            array('page_title', 'field', 1),
            array('url_key', 'field', 1),
            array('content', 'field', 1),
            array('store_view', 'multiselect', 1),
            array('widget_type', 'dropdown', 2)
        );
    }

    /**
     * <p>Creates Pages with same URL Key</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Pages page</p>
     * <p>2. Create page with required fields</p>
     * <p>3. Create page with the same URL Key</p>
     * <p>Expected result</p>
     * <p>Page with the same URL Key is not created</p>
     *
     * @depends withRequiredFields
     * @test
     */
    public function withExistUrlkey($pageData)
    {
        //Steps
        $this->navigate('manage_cms_pages');
        $this->cmsPagesHelper()->createCmsPage($pageData);
        //Verification
        $this->assertMessagePresent('error', 'existing_url_key');
    }

    /**
     * <p>Creates Pages with numbers in URL Key</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Pages page</p>
     * <p>2. Create page with required fields</p>
     * <p>Expected result</p>
     * <p>Page with the numbers in URL Key is not created</p>
     *
     * @dataProvider dataWrongUrlKey
     * @depends withRequiredFields
     * @test
     */
    public function withWrongUrlKey($urlValue, $messageType)
    {
        //Data
        $pageData = $this->loadData('new_cms_page_req', array('url_key' => $urlValue));
        //Steps
        $this->navigate('manage_cms_pages');
        $this->cmsPagesHelper()->createCmsPage($pageData);
        //Verification
        if ($messageType == 'error') {
            $this->assertMessagePresent('error', 'invalid_url_key_with_numbers_only');
        } else {
            $this->addFieldIdToMessage('field', 'url_key');
            $this->assertMessagePresent('validation', 'invalid_urk_key_spec_sym');
        }
    }

    public function dataWrongUrlKey()
    {
        return array(
            array($this->generate('string', 10, ':digit:'), 'error'),
            array($this->generate('string', 10, ':punct:'), 'validation')
        );
    }

}
