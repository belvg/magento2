<?php

class Mage_Eav_Model_Mysql4_Entity_Attribute extends Mage_Core_Model_Resource_Abstract 
{
    public function __construct()
    {
        $this->_setResource('eav');
        $this->_setMainTable('attribute');
    }
    
    public function loadByName($object, $entityTypeId, $name)
    {
        $read = $this->getConnection('read');
        
        $select = $read->select()->from($this->getMainTable())
            ->where('entity_type_id=?', $entityTypeId)
            ->where('attribute_name=?', $name);
        $data = $read->fetchRow($select);
        
        if (!$data) {
            return false;
        }
        
        $object->setData($data);

        $this->_afterLoad($object);
        
        return true;
    }
}