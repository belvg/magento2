<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Backend\Test\TestCase;

use Magento\Backend\Test\Fixture\GlobalSearch;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for GlobalSearchEntity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create customer
 * 2. Create two simple products
 * 3. Create order with one of created simple product
 *
 * Steps:
 * 1. Login to backend
 * 2. Click on Search button on the top of page
 * 3. Fill in data according dataSet
 * 4. Perform assertions
 *
 * @group Search_Core_(MX)
 * @ZephyrId MAGETWO-28457
 */
class GlobalSearchEntityTest extends Injectable
{
    /**
     * Backend Dashboard page
     *
     * @var Dashboard
     */
    protected $dashboard;

    /**
     * Preparing pages for test
     *
     * @param Dashboard $dashboard
     * @return void
     */
    public function __inject(Dashboard $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    /**
     * Run Global Search Entity Test
     *
     * @param GlobalSearch $search
     * @return void
     */
    public function test(GlobalSearch $search)
    {
        //Steps:
        $this->dashboard->open();
        $this->dashboard->getAdminPanelHeader()->search($search->getQuery());
    }
}
