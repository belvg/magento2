<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category   Enterprise
 * @package    Enterprise_CustomerSegment
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://www.magentocommerce.com/license/enterprise-edition
 */
class Enterprise_CustomerSegment_Model_Mysql4_Segment_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
   /**
     * Intialize collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('enterprise_customersegment/segment');
    }

    public function addIsActiveFilter($value)
    {
        $this->getSelect()->where('main_table.is_active = ?', $value);

        return $this;
    }

    public function addEventFilter($eventName)
    {
        $this->getSelect()->joinInner(
            array('evt'=>$this->getTable('enterprise_customersegment/event')),
            'main_table.segment_id = evt.segment_id',
            array()
        );
        $this->getSelect()->where('evt.event = ?', $eventName);

        return $this;
    }

    public function addWebsiteFilter($websiteId)
    {
        $this->getSelect()->where('website_id = ?', $websiteId);

        return $this;
    }
}
