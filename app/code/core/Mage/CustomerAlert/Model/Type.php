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

class Mage_CustomerAlert_Model_Type extends Mage_Core_Model_Abstract
{
    public function __construct()
    {
        $this->_init('customeralert/type', 'id');
    }
    
    public function getParamValues($withCustomerId = false)
    {
        $value = array();
        if($this->getData('product_id')){
            $value['product_id'] = $this->getData('product_id');
        }
        
        if($this->getData('store_id')){
            $value['store_id'] = $this->getData('store_id');
        }
        if($this->getData('type')){
            $value['type'] = $this->getData('type');
        }
        if($this->getData('customer_id')==-1 || ($withCustomerId && $this->getData('customer_id'))){
            $value['customer_id'] = $this->getData('customer_id');
        }
        return $value;
    }
    
    public function setParamValues($data)
    {
        $this->addData(array(
            'product_id' => $data['product_id'],
            'store_id' => $data['store_id'],
        ));
        return $this;
    }
    
    public function loadAllByParam()
    {
        return $this->_getResource()->loadByParam($this);
    }
    
    public function loadByParam()
    {
        $data = $this->loadAllByParam();
        if(isset($data[0])){
            $this->addData($data[0]);
        }
        return $this; 
    }
    
    public function isChecked()
    {
        return ($this->getId()) ? true : false;
    }
    
    public function getCheck()
    {
        return  Mage::getModel('customeralert/alert_check')
            ->addData($this->getParamValues());   
    }
    
    public function getAlertHappened()
    {
        $this->_alertHappen = $this->getCheck()->isAlertHappened();
        return $this->_alertHappen;
    }
    
    public function save()
    {
        $this->loadByParam();
        parent::save();
    }
    
    public function addAlert($check, $newValue = null, $oldValue = null, $customerGroupId = null, $date = null)
    {
        if($this->getStoreId()>0) {
            if(!$date)$date = now();
            $alertCheck = Mage::getModel('customeralert/alert_check')
                    ->addData($this->getParamValues());
            if($newValue || $oldValue) {    
                $alertCheck->addData(array('new_value'=>$newValue,'old_value'=>$oldValue,'date'=>$date,'customer_group_id'=>$customerGroupId));
            }     
            if($check) {
                $alertCheck->addAlert();
            } else {
                $alertCheck->removeAlert();
            }
        }
        return $this;
    }
    
    public function getAlertChangedValues()
    {
        return Mage::getModel('customeralert/alert_check')
            ->addData($this->getParamValues())
            ->loadByParam();
    }
    
    public function addCustomersToAlertQueue()
    {
        if($this->getAlertHappened() || $this->isCustomerGroupIds()){
            $customer = Mage::getResourceModel('customeralert/customer_collection')
                -> setAlert ($this)
                -> load();
            if($customer) {
                Mage::getModel('customeralert/queue')
                    ->addCustomersToAlertQueue($customer, $this->getCheck()->addToQueue($this->isCustomerGroupIds()));
                return true;
            } else {
                return false;
            }
                    
        }
        return false;
    }
    
    public function isCustomerGroupIds()
    {
        $rows = Mage::getModel('customeralert/alert_check')
            ->addData($this->getParamValues())
            ->loadByParam();
        $customersGroupIds = array();
        foreach($rows as $val) {
            if($val['customer_group_id']!=-1) {
                return true;
            }
        }
        return false;
    }
    
    public function getCustomerAlerts()
    {
        return $this->_getResource()->getCustomerAlerts($this);      
    }
    
}
