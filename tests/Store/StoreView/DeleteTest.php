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
 * Delete Store View in Backend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Store_StoreView_DeleteTest extends Mage_Selenium_TestCase
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
     * <p>Navigate to System -> Manage Stores</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_stores');
    }

    /**
     * <p>Delete Store View With creating DB backup</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "System->Manage Stores";</p>
     * <p>2. Select created Store View from the grid and open it;</p>
     * <p>3. Click "Delete Store View" button;</p>
     * <p>4. Select "Yes" on Backup Options page;</p>
     * <p>5. Click "Delete Store View" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears - "Database was successfuly backed up. The store view has been deleted."</p>
     *
     * @test
     */
    public function deleteStoreViewBackup()
    {
        $this->markTestIncomplete('@TODO');
    }

    /**
     * <p>Delete Store View Without creating DB backup</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "System->Manage Stores";</p>
     * <p>2. Select created Store View from the grid and open it;</p>
     * <p>3. Click "Delete Store View" button;</p>
     * <p>4. Select "No" on Backup Options page;</p>
     * <p>5. Click "Delete Store View" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears - "The store view has been deleted."</p>
     *
     * @test
     */
    public function deleteStoreViewWithoutBackup()
    {
        $this->markTestIncomplete('@TODO');
    }

    /**
     * <p>Delete Store View with assigned product</p>
     * <p>Preconditions:</p>
     * <p>Create product and assign it to created store view</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "System->Manage Stores";</p>
     * <p>2. Select created Store View from the grid and open it;</p>
     * <p>3. Click "Delete Store View" button;</p>
     * <p>4. Select "No" on Backup Options page;</p>
     * <p>5. Click "Delete Store View" button;</p>
     * <p>Expected result:</p>
     * <p>Success message appears - "The store view has been deleted."</p>
     *
     * @test
     */
    public function deleteStoreViewWithAssignedProduct()
    {
        $this->markTestIncomplete('@TODO');
    }

    /**
     * <p>Store View that cannot be deleted</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "System->Manage Stores";</p>
     * <p>2. Select Default Store View from the grid (Store view should be only one into the grid) and open it;</p>
     * <p>Expected result:</p>
     * <p>Verify that "Delete Store View" button is absent on the page;</p>
     *
     * @test
     */
    public function undeletableStoreView()
    {
        $this->markTestIncomplete('@TODO');
    }

}
