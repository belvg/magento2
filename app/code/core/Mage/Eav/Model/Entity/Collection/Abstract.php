<?php

/**
 * Entity/Attribute/Model - collection abstract
 *
 * @package    Mage
 * @subpackage Mage_Eav
 * @author     Moshe Gurvich moshe@varien.com>
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 */
class Mage_Eav_Model_Entity_Collection_Abstract implements IteratorAggregate
{
    /**
     * Read connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_read;
    
    /**
     * Write connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_write;
    
    /**
     * Entity object to define collection's attributes
     *
     * @var Mage_Eav_Model_Entity_Abstract
     */
    protected $_entity;
    
    /**
     * Attributes to be fetched for objects in collection
     *
     * @var array
     */
    protected $_selectAttributes=array();
    
    /**
     * Attributes to be filtered order sorted by
     *
     * @var array
     */
    protected $_filterAttributes=array();
    
    /**
     * Object template to be used for collection items
     *
     * @var Varien_Object
     */
    protected $_object;
    
    /**
     * Collection's Zend_Db_Select object
     *
     * @var Zend_Db_Select
     */
    protected $_select;
    
    /**
     * Array of objects in the collection
     *
     * @var array
     */
    protected $_items = array();
    
    /**
     * Record number where the page starts
     *
     * @var integer
     */
    protected $_pageStart;
    
    /**
     * Number of records on the page
     *
     * @var integer
     */
    protected $_pageSize;
    
    protected $_rowCount;
    
    protected $_joinEntities = array();
    
    protected $_joinAttributes = array();

    /**
     * Set connections for entity operations
     *
     * @param Zend_Db_Adapter_Abstract $read
     * @param Zend_Db_Adapter_Abstract $write
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function setConnection(Zend_Db_Adapter_Abstract $read, Zend_Db_Adapter_Abstract $write=null)
    {
        $this->_read = $read;
        $this->_write = $write ? $write : $read;
        return $this;
    }
    
    /**
     * Set entity to use for attributes
     *
     * @param Mage_Eav_Model_Entity_Abstract $entity
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function setEntity($entity)
    {
        if ($entity instanceof Mage_Eav_Model_Entity_Abstract) {
            $this->_entity = $entity;
        } elseif (is_string($entity) || $entity instanceof Mage_Core_Model_Config_Element) {
            $this->_entity = Mage::getModel('eav/entity')->setType($entity);
        }
        $this->_read = $entity->getReadConnection();
        $this->_write = $entity->getWriteConnection();

        if ($entity->getTypeId()) {
            $this->addAttributeToFilter('entity_type_id', $entity->getTypeId());
        }
        return $this;
    }
    
    /**
     * Get collection's entity object
     * 
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function getEntity()
    {
        if (empty($this->_entity)) {
            throw Mage::exception('Mage_Eav', 'Entity is not initialized');
        }
        return $this->_entity;
    }
    
    /**
     * Set template object for the collection
     *
     * @param Varien_Object $object
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function setObject($object=null)
    {
        if (empty($object)) {
            $object = new Varien_Object();
        } elseif (is_string($object)) {
            $object = Mage::getModel($object);
        }
        if (!$object instanceof Varien_Object) {
            throw Mage::exception('Mage_Eav', 'Invalid object supplied');
        }
        
        $this->_object = $object;
        
        return $this;
    }
    
    /**
     * Get template object
     *
     * @return Varien_Object
     */
    public function getObject()
    {
        /*
        if (!$this->_object && $this->_entity && $this->_entity->getObject()) {
            $this->setObject($this->_entity->getObject());
        }
        */
        if (!$this->_object) {
            $this->setObject();
        }
        return $this->_object;
    }

    
    /**
     * Retrieve array of object collection items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }
    
    /**
     * Add an object to the collection
     *
     * @param Varien_Object $object
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addItem(Varien_Object $object)
    {
        if (get_class($object)!==get_class($this->getObject())) {
            throw Mage::exception('Mage_Eav', 'Attempt to add an invalid object');
        }
        
        //$entityId = $row[$this->getEntity()->getEntityIdField()];
        if ($entityId = $object->getId()) {
            $this->_items[$entityId] = $object;
        }
        else {
            $this->_items[] = $object;
        }        
        
        return $this;
    }
    
    /**
     * Reset zend db select instance
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function resetSelect()
    {
        $this->_select = $this->_read->select();
        $this->_select->from(array('e'=>$this->getEntity()->getEntityTable()));
        return $this;
    }
    
    /**
     * Get zend db select instance
     *
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
        if (empty($this->_select)) {
            $this->resetSelect();
        }
        return $this->_select;
    }
    
    /**
     * Add attribute filter to collection
     *
     * If $attribute is an array will add OR condition with following format:
     * array(
     *     array('attribute'=>'firstname', 'like'=>'test%'),
     *     array('attribute'=>'lastname', 'like'=>'test%'),
     * )
     * 
     * @see self::_getConditionSql for $condition
     * @param string|array $attribute
     * @param null|string|array $condition
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addAttributeToFilter($attribute, $condition=null)
    {
        if (is_array($attribute)) {
            $sqlArr = array();
            foreach ($attribute as $condition) {
                $sqlArr[] = $this->_getAttributeConditionSql($condition['attribute'], $condition);
            }
            $conditionSql = '('.join(') OR (', $sqlArr).')';
        } elseif (is_string($attribute)) {
            if (is_null($condition)) {
                throw Mage::exception('Mage_Eav', 'Invalid condition');
            }
            $conditionSql = $this->_getAttributeConditionSql($attribute, $condition);
        }
        $this->getSelect()->where($conditionSql);
        return $this;
    }
    
    /**
     * Add attribute to sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addAttributeToSort($attribute, $dir='asc')
    {
        if (isset($this->_joinAttributes[$attribute])) {
            $attrInstance = $this->_joinAttributes[$attribute]['attribute'];
            $entityField = $this->_getAttributeTableAlias($attribute).'.'.$attrInstance->getName();
        } else {
            $attrInstance = $this->getEntity()->getAttribute($attribute);
            $entityField = 'e.'.$attribute;
        }
        if ($attrInstance->getBackend()->isStatic()) {
            $this->getSelect()->order($entityField.' '.$dir);
        } else {
            $this->_addAttributeJoin($attribute);
            $this->getSelect()->order($this->_getAttributeTableAlias($attribute).'.value '.$dir);
        }
        return $this;
    }
    
    /**
     * Add attribute to entities in collection
     *
     * If $attribute=='*' select all attributes
     * 
     * @param array|string|integer|Mage_Core_Model_Config_Element $attribute
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addAttributeToSelect($attribute)
    {
        if (is_array($attribute)) {
            foreach ($attribute as $a) {
                $this->addAttribute($a);
            }
        } elseif ('*'===$attribute) {
            $attributes = $this->getEntity()->loadAllAttributes($this->getObject())->getAttributesByName();
            foreach ($attributes as $attrName=>$attr) {
                $this->_selectAttributes[$attrName] = $attr->getId();
            }
        } else {
            if (isset($this->_joinAttributes[$attribute])) {
                $attrInstance = $this->_joinAttributes[$attribute]['attribute'];
            } else {
                $attrInstance = $this->getEntity()->getAttribute($attribute);
            }
            $this->_selectAttributes[$attrInstance->getName()] = $attrInstance->getId();
        }
        return $this;
    }
    
    /**
     * Add attribute from joined entity to select
     * 
     * Examples:
     * ('billing_firstname', 'default_billing', 'customer_address/firstname')
     * ('billing_lastname', 'default_billing', 'customer_address/lastname')
     * ('shipping_lastname', 'default_billing', 'customer_address/lastname')
     * ('shipping_postalcode', 'default_shipping', 'customer_address/postalcode')
     * ('shipping_city', 'default_shipping', $cityAttribute)
     * ('shipping_country', 'default_shipping', 'country_id', $addressEntity)
     * 
     * Developer is encouraged to use existing instances of attributes and entities
     * After first use of string entity name it will be cached in the collection
     *
     * @todo connect between joined attributes of same entity
     * @param string $alias alias for the joined attribute
     * @param string $fk attribute of the main entity to link with joined entity_id
     * @param string|Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param string|Mage_Eav_Model_Entity_Abstract|null $entity
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function joinAttribute($alias, $fk, $attribute, $entity=null)
    {
        // validate alias
        if (isset($this->_joinAttributes[$alias])) {
            throw Mage::exception('Mage_Eav', 'Invalid alias, already exists in joined attributes');
        }

        // validate foreign key
        if (is_string($fk)) {
            $fk = $this->getEntity()->getAttribute($fk);
        }
        if (!$fk || !$fk->getId()) {
            throw Mage::exception('Mage_Eav', 'Invalid foreign key');
        }
        
        // try to explode combined entity/attribute if supplied
        if (is_string($attribute)) {
            $attrArr = explode('/', $attribute);
            if (empty($entity) && isset($attrArr[1])) {
                $entity = $attrArr[0];
                $attribute = $attrArr[1];
            }
        }

        // validate entity
        if (empty($entity) && $attribute instanceof Mage_Eav_Model_Entity_Attribute_Abstract) {
            $entity = $attribute->getEntity();
        } elseif (is_string($entity)) {
            // retrieve cached entity if possible
            if (isset($this->_joinEntities[$entity])) {
                $entity = $this->_joinEntities[$entity];
            } else {
                $entity = Mage::getModel('eav/entity')->setType($attrArr[0]);
            }
        }
        if (!$entity || !$entity->getTypeId()) {
            throw Mage::exception('Mage_Eav', 'Invalid entity type');
        }
        // cache entity
        if (!isset($this->_joinEntities[$entity->getType()])) {
            $this->_joinEntities[$entity->getType()] = $entity;
        }
        
        // validate attribute
        if (is_string($attribute)) {
            $attribute = $entity->getAttribute($attribute);
        }
        if (!$attribute) {
            throw Mage::exception('Mage_Eav', 'Invalid attribute type');
        }
        
        // add joined attribute
        $this->_joinAttributes[$alias] = array(
            'fk'=>$fk,
            'attribute'=>$attribute,
        );
        
        $this->_addAttributeJoin($alias);
        
        return $this;
    }
    
    /**
     * Remove an attribute from selection list
     *
     * @param string $attribute
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function removeAttributeToSelect($attribute=null)
    {
        if (is_null($attribute)) {
            $this->_selectAttributes = array();
        } else {
            unset($this->_selectAttributes[$attribute]);
        }
        return $this;
    }
    
    /**
     * Set collection page start and records to show
     *
     * @param integer $pageNum
     * @param integer $pageSize
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function setPage($pageNum, $pageSize)
    {
        //$this->getSelect()->limitPage($pageNum, $pageSize);
        $this->setCurPage($pageNum)
            ->getPageSize($pageSize);
        return $this;
    }

    /**
     * Load collection data into object items
     *
     * @param integer $storeId
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function load()
    {
        if (!$this->_read) {
            throw Mage::exception('Mage_Eav', 'No connection available');
        }
        
        $this->_loadEntities();
        $this->_loadAttributes();

        return $this;
    }
    
    /**
     * Save all the entities in the collection
     *
     * @todo make batch save directly from collection
     */
    public function save()
    {
        #$this->walk('save');
        foreach ($this->getItems() as $item) {
            $this->getEntity()->save($item);
        }
        return $this;
    }
    
    
    /**
     * Delete all the entities in the collection
     *
     * @todo make batch delete directly from collection
     */
    public function delete()
    {
        #$this->walk('delete');
        foreach ($this->getItems() as $k=>$item) {
            $this->getEntity()->delete($item);
            unset($this->_items[$k]);
        }
        return $this;
    }
    
    /**
     * Import 2D array into collection as objects
     * 
     * If the imported items already exist, update the data for existing objects
     *
     * @param array $arr
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function importFromArray($arr)
    {
        $entityIdField = $this->getEntity()->getEntityIdField();
        foreach ($arr as $row) {
            $entityId = $row[$entityIdField];
            if (!isset($this->_items[$entityId])) {
                $this->_items[$entityId] = clone $this->getObject();
                $this->_items[$entityId]->setData($row);
            }  else {
                $this->_items[$entityId]->addData($row);
            }
        }
        return $this;
    }
    
    /**
     * Get collection data as a 2D array
     *
     * @return array
     */
    public function exportToArray()
    {
        $result = array();
        $entityIdField = $this->getEntity()->getEntityIdField();
        foreach ($this->getItems() as $item) {
            $result[$item->getData($entityIdField)] = $item->getData();
        }
        return $result;
    }
    
    /**
     * Walk through the collection and run method with optional arguments
     *
     * Returns array with results for each item
     * 
     * @param string $method
     * @param array $args
     * @return array
     */
    public function walk($method, array $args=array())
    {
        $results = array();
        foreach ($this->getItems() as $id=>$item) {
            $results[$id] = call_user_func_array(array($item, $method), $args);
        }
        return $results;
    }

    
    /**
     * Load entities records into items
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function _loadEntities()
    {
        $entity = $this->getEntity();
        $entityIdField = $entity->getEntityIdField();
        
        if ($this->_pageStart && $this->_pageSize) {
            $this->getSelect()->limitPage($this->_pageStart, $this->_pageSize);
        }
        
        $rows = $this->_read->fetchAll($this->getSelect());
        if (!$rows) {
            return $this;
        }

        foreach ($rows as $v) {
            $object = clone $this->getObject();
            $this->_items[$v[$entityIdField]] = $object->setData($v);
        }
        return $this;
    }
    
    /**
     * Load attributes into loaded entities
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function _loadAttributes()
    {
        if (empty($this->_items) || empty($this->_selectAttributes)) {
            return $this;
        }
        
        $entity = $this->getEntity();
        $entityIdField = $entity->getEntityIdField();
        
        $condition = "entity_type_id=".$entity->getTypeId();
        $condition .= " and ".$this->_read->quoteInto("$entityIdField in (?)", array_keys($this->_items));
        $condition .= " and ".$this->_read->quoteInto("store_id in (?)", $entity->getSharedStoreIds());
        $condition .= " and ".$this->_read->quoteInto("attribute_id in (?)", $this->_selectAttributes);

        $attrById = array();
        foreach ($entity->getAttributesByTable() as $table=>$attributes) {
            $sql = "select $entityIdField, attribute_id, value from $table where $condition";
            $values = $this->_read->fetchAll($sql);
            if (empty($values)) {
                continue;
            }
            
            foreach ($values as $v) {
                if (!isset($this->_items[$v[$entityIdField]])) {
                    throw Mage::exception('Mage_Eav', 'Data integrity: No header row found for attribute');
                }
                if (!isset($attrById[$v['attribute_id']])) {
                    $attrById[$v['attribute_id']] = $entity->getAttribute($v['attribute_id'])->getName();
                }
                $this->_items[$v[$entityIdField]]->setData($attrById[$v['attribute_id']], $v['value']);
            }
        }
        
        return $this;
    }
    
    /**
     * Get alias for attribute value table
     *
     * @param string $attributeName
     * @return string
     */
    protected function _getAttributeTableAlias($attributeName)
    {
        return '_table_'.$attributeName;
    }

    /**
     * Add attribute value table to the join if it wasn't added previously
     *
     * @param string $attributeName
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _addAttributeJoin($attributeName)
    {
        if (!empty($this->_filterAttributes[$attributeName])) {
            return $this;
        }
        
        if (isset($this->_joinAttributes[$attributeName])) {
            $attribute = $this->_joinAttributes[$attributeName]['attribute'];
            $entity = $attribute->getEntity();
            $entityIdField = $entity->getEntityIdField();
            $fkAttribute = $this->_joinAttributes[$attributeName]['fk'];
            if ($fkAttribute->getBackend()->isStatic()) {
                $fk = "e.".$fkAttribute->getName();
            } else {
                $this->_addAttributeJoin($fkAttribute->getName());
                $fk = $this->_getAttributeTableAlias($fkAttribute->getName()).".value";
            }
        } else {
            $entity = $this->getEntity();
            $entityIdField = $entity->getEntityIdField();
            $attribute = $entity->getAttribute($attributeName);
            $fk = "e.$entityIdField";
        }
        
        if (!$attribute) {
            throw Mage::exception('Mage_Eav', 'Invalid attribute name: '.$attributeName);
        }
        
        $select = $this->getSelect();
        
        $t = $this->_getAttributeTableAlias($attributeName);
        $select->join(
            array($t => $attribute->getBackend()->getTable()),
            "$t.$entityIdField = $fk",
            array($attributeName=>"$t.value")
        );
        #$select->where("$t.entity_type_id=?", $entity->getTypeId());
        $select->where("$t.store_id in (?)", $entity->getSharedStoreIds());
        $select->where("$t.attribute_id=?", $attribute->getId());
        
        $this->removeAttributeToSelect($attributeName);
        
        $this->_filterAttributes[$attributeName] = $attribute->getId();
        
        return $this;
    }

    /**
     * Build SQL statement for condition
     *
     * If $condition integer or string - exact value will be filtered
     *
     * If $condition is array is - one of the following structures is expected:
     * - array("from"=>$fromValue, "to"=>$toValue)
     * - array("like"=>$likeValue)
     * - array("neq"=>$notEqualValue)
     * - array("in"=>array($inValues))
     * - array("nin"=>array($notInValues))
     *
     * If non matched - sequential array is expected and OR conditions
     * will be built using above mentioned structure
     *
     * @param string $fieldName
     * @param integer|string|array $condition
     * @return string
     */
    protected function _getConditionSql($fieldName, $condition) {
        $sql = '';
        if (is_array($condition)) {
            if (!empty($condition['from']) && !empty($condition['to'])) {
                $sql = $this->_read->quoteInto("$fieldName between ?", $condition['from']);
                $sql = $this->_read->quoteInto("$sql and ?", $condition['to']);
            } elseif (!empty($condition['neq'])) {
                $sql = $this->_read->quoteInto("$fieldName != ?", $condition['neq']);
            } elseif (!empty($condition['like'])) {
                $sql = $this->_read->quoteInto("$fieldName like ?", $condition['like']);
            } elseif (!empty($condition['in'])) {
                $sql = $this->_read->quoteInto("$fieldName in (?)", $condition['in']);
            } elseif (!empty($condition['nin'])) {
                $sql = $this->_read->quoteInto("$fieldName not in (?)", $condition['nin']);
            } else {
                $orSql = array();
                foreach ($condition as $orCondition) {
                    $orSql[] = "(".$this->_getConditionSql($fieldName, $orCondition).")";
                }
                $sql = "(".join(" or ", $orSql).")";
            }
        } else {
            $sql = $this->_read->quoteInto("$fieldName = ?", $condition);
        }
        return $sql;
    }
    
    /**
     * Get condition sql for the attribute
     *
     * @see self::_getConditionSql
     * @param string $attribute
     * @param mixed $condition
     * @return string
     */
    protected function _getAttributeConditionSql($attribute, $condition)
    {
        // process linked attribute
        if (isset($this->_joinAttributes[$attribute])) {
            $entity = $attribute->getEntity();
            $entityTable = $entity->getEntityTable();
        } else {
            $entity = $this->getEntity();
            $entityTable = 'e';
        }
        
        if ($entity->isAttributeStatic($attribute)) {
            $conditionSql = $this->_getConditionSql('e.'.$attribute, $condition);
        } else {
            $this->_addAttributeJoin($attribute);
            $conditionSql = $this->_getConditionSql($this->_getAttributeTableAlias($attribute).'.value', $condition);
        }
        return $conditionSql;
    }
    
    public function setPageSize($pageSize)
    {
        $this->_pageSize = $pageSize;
        return $this;
    }
    
    public function setCurPage($page)
    {
        $this->_pageStart = $page;
        return $this;
    }
    
    public function getLastPageNumber()
    {
        $collectionSize = $this->getSize();
        if (0 === $collectionSize) {
            return 1;
        } 
        elseif($this->_pageSize) {
            return ceil($collectionSize/$this->_pageSize);
        }
        else{
            return 1;
        }
    }
    
    public function getCurPage()
    {
        return $this->_pageStart;
    }
    
    public function getPageSize()
    {
        return $this->_pageSize;
    }
    
    /**
     * Get sql for get record count
     *
     * @return  string
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);

        $sql = $countSelect->__toString();
        $sql = preg_replace('/^select\s+.+?\s+from\s+/is', 'select count(*) from ', $sql);
        return $sql;
    }
    
    public function getSize()
    {
        if (is_null($this->_rowCount)) {
            $this->_rowCount = $this->_read->fetchOne($this->getSelectCountSql());
        }
        return $this->_rowCount;
    }
    
    public function setOrder($attribute, $dir='desc')
    {
        return $this->addAttributeToSort($attribute, $dir);
    }
    
    public function getIterator()
    {
        return new ArrayIterator($this->_items);
    }
}