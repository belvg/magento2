<?php

class Mage_Sales_Model_Mysql4_Counter
{   
    protected $_write;
    protected $_read;
    protected $_counterTable;
    
    public function __construct()
    {
        $this->_read = Mage::getSingleton('core/resource')->getConnection('sales_read');
        $this->_write = Mage::getSingleton('core/resource')->getConnection('sales_write');
        $this->_counterTable = Mage::getSingleton('core/resource')->getTableName('sales/counter');
    }
    
    public function getCounter($type, $store=null, $increase=true)
    {
        if (is_null($store)) {
            $store = Mage::getSingleton('core/store')->getId();
        }

        $condition = $this->_write->quoteInto("counter_type=?", $type)." and "
            .$this->_write->quoteInto("store_id=?", $store);

        $this->_write->beginTransaction();
        try {
            $value = $this->_write->fetchOne("select counter_value from ".$this->_counterTable." where ".$condition);
            if (!$value) {
                $value = 1;
                $this->_write->insert($this->_counterTable, array("counter_type"=>$type, "store_id"=>$store, "counter_value"=>$increase ? $value+1 : $value));
            } elseif ($increase) {
                $this->_write->update($this->_counterTable, array("counter_value"=>new Zend_Db_Expr("counter_value+1")), $condition);
            }
            $this->_write->commit();
            #$value = str_pad($value, 8, '0', STR_PAD_LEFT);

        } catch (Mage_Core_Exception $e) {
            throw $e;
        } catch (Exception $e) {
            $this->_write->rollBack();
            $value = false;
        }

        return $value;
    }
}