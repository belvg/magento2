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
 * @category   Mage
 * @package    Mage_Cms
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer alert type model
 *
 * @category   Mage
 * @package    Mage_CustomerAlert
 * @author     Vasily Selivanov <vasily@varien.com>
 */

class Mage_CustomerAlert_Model_Mysql4_Type extends Mage_Core_Model_Mysql4_Abstract
{
    public function __construct()
    {
        $this->_init('customeralert/alert','id');
        parent::__construct();
    }
    
    public function loadByParam(Mage_Core_Model_Abstract $model, $fetch = 'fetchAll')
    {
        $data = $model->getData();
        $read = $this->_getReadAdapter();
        $select = $read
            ->select()
            ->from($this->getMainTable());
        
        foreach ($model->getParamValues(true) as $key=>$val) {
            $select->where($key.' = ?',$val);
        }
        return $read->$fetch($select);
    }
    
    public function getCustomerAlerts(Mage_Core_Model_Abstract $model)
    {
        $data = $model->getData();
        $read = $this->_getReadAdapter();
        $select = $read
            ->select()
            ->from($this->getMainTable(),'type')
            ->distinct();
        foreach ($model->getParamValues() as $key=>$val) {
            $select->where($key.' = ?',$val);
        }
        return $read->fetchAll($select);
    }
    
    public function getAlertsForCronChecking()
    {
        $read = $this->_getReadAdapter();
        $select = $read
            ->select()
            ->from($this->getMainTable(),array('type','store_id','product_id'))
            ->distinct();
        return $read->fetchAll($select);
    }
}
