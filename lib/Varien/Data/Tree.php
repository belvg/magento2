<?php
/**
 * Data tree
 *
 * @package    Ecom
 * @subpackage Data
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 */
class Varien_Data_Tree
{
    /**
     * Nodes collection
     *
     * @var Varien_Data_Tree_Node_Collection
     */
    protected $_nodes;
    
    public function __construct() 
    {
        $this->_nodes = new Varien_Data_Tree_Node_Collection($this);
    }
    
    public function getTree()
    {
        return $this;
    }
    
    public function load($parentNode, $recursive=false) {}
    public function loadNode($nodeId) {}
    public function appendChild($parentNode, $prevNode=null) {}
    
    public function addNode($node, $parent=null)
    {
        $this->_nodes->add($node);
        if (!is_null($parent) && ($parent instanceof Varien_Data_Tree_Node) ) {
            $parent->addChild($node);
        }
    }
    
    public function moveNodeTo($node, $parentNode, $prevNode=null) {}
    public function copyNodeTo($node, $parentNode, $prevNode=null) {}
    public function removeNode($node) {}
    public function createNode($parentNode, $prevNode=null) {}
    
    public function getPath($node) {}
    public function getChild($node) {}
    public function getChildren($node) {}
    
    public function getNodes()
    {
        return $this->_nodes;
    }
}