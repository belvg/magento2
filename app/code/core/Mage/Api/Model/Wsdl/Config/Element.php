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
 * @category   Mage
 * @package    Mage_Core
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Wsdl element model
 *
 * @category   Mage
 * @package    Mage_Core
 */
class Mage_Api_Model_Wsdl_Config_Element extends Varien_Simplexml_Element
{
    public function extend($source, $overwrite=false)
    {
        if (!$source instanceof Varien_Simplexml_Element) {
            return $this;
        }
        foreach ($this->getChildren($source) as $namespace => $children) {
            foreach ($children as $child) {
                $this->extendChild($child, $overwrite, $namespace);
            }
        }

        return $this;
    }

    /**
     * Extends one node
     *
     * @param Varien_Simplexml_Element $source
     * @param boolean $overwrite
     * @return Varien_Simplexml_Element
     */
    public function extendChild($source, $overwrite=false, $elmNamespace = '')
    {
        // this will be our new target node
        $targetChild = null;

        // name of the source node
        $sourceName = $source->getName();
        // here we have children of our source node
        $sourceChildren = $this->getChildren($source);

        if (!$source->hasChildren()) {
            // handle string node
            $elm = $this->_getElementByName($source, $elmNamespace);
            if (!is_null($elm)) {

                // if target already has children return without regard
                if ($this->getChildren($elm)) {
                    return $this;
                }
                if ($overwrite) {
//                    unset($this->$sourceName);
                    unset($elm);
                } else {
                    return $this;
                }
            }
//            Mage::log($elmNamespace);
            $targetChild = $this->addChild($sourceName, $source->xmlentities(), $elmNamespace);
            $targetChild->setParent($this);
            foreach ($this->getAttributes($source) as $namespace => $attributes) {
                foreach ($attributes as $key => $value) {
                    $targetChild->addAttribute($key, $this->xmlentities($value), $namespace);
                }
            }
            return $this;
        }

        $elm = $this->_getElementByName($source, $elmNamespace);
        if (!is_null($elm)) {
            $targetChild = $elm;
        }
        if (is_null($targetChild)) {
            // if child target is not found create new and descend
            $targetChild = $this->addChild($sourceName, null, $elmNamespace);
            $targetChild->setParent($this);
            foreach ($this->getAttributes($source) as $namespace => $attributes) {
                foreach ($attributes as $key => $value) {
                    $targetChild->addAttribute($key, $this->xmlentities($value), $namespace);
                }
            }
        }

        foreach ($sourceChildren as $elmNamespace => $children) {
            foreach ($children as $childKey => $childNode) {
                $targetChild->extendChild($childNode, $overwrite, $elmNamespace);
            }
        }

        return $this;
    }

    /**
     * Enter description here...
     *
     * @param Varien_Simplexml_Element $source
     * @return bool
     */
    public function compareAttributes($source, $elmNamespace = '', $flag = false)
    {
        $sourceName = $source->getName();
        $elm = $this->getElementByName($sourceName, $elmNamespace);
        if (!is_null($elm)) {
            $allAttributes = $this->getAttributes($elm);
            $sourceAllAttributes = $this->getAttributes($source);
            if (count($allAttributes) != count($sourceAllAttributes)) {
                return false;
            }
            foreach ($allAttributes as $namespace => $attributes) {
                if (isset($sourceAllAttributes[$namespace]) && count($attributes) != count($sourceAllAttributes[$namespace])) {
                    return false;
                }
                foreach ($attributes as $key => $value) {
                    if (is_null($source->getAttribute($key, $namespace)) || $source->getAttribute($key, $namespace) != $value) {
                        return false;
                    }
                }
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * Enter description here...
     *
     * @param Varien_Simplexml_Element $source
     * @return array
     */
    public function getAttributes($source, $namespace = null)
    {
        $attributes = array();
        if (!is_null($namespace)) {
            $attributes[$namespace] = $source->attributes($namespace);
            return $attributes;
        }
        $namespaces = $source->getNamespaces(true);
        $attributes[''] = $source->attributes('');
        foreach ($namespaces as $key => $value) {
            if ($key == '' || $key == 'soap') {
                continue;
            }
            $attributes[$value] = $source->attributes($value);
        }
        return $attributes;
    }

    /**
     * Enter description here...
     *
     * @param Varien_Simplexml_Element $source
     */
    public function getChildren($source)
    {
        $children = array();
        $namespaces = $source->getNamespaces(true);
        $children[''] = $source->children('');
        foreach ($namespaces as $key => $value) {
            if ($key == '' || $key == 'wsdl') {
                $value = '';
                continue;
            }
            $children[$value] = $source->children($value);
        }
        return $children;
    }

    public function hasChildren()
    {
        if (!$this->getChildren($this)) {
            return false;
        }

        // simplexml bug: @attributes is in children() but invisible in foreach
        foreach ($this->getChildren($this) as $namespace => $children) {
            foreach ($children as $k => $child) {
                return true;
            }
        }
        return false;
    }

    public function getElementByName($name, $namespace = '')
    {
        foreach ($this->children($namespace) as $k => $child) {
            if ($child->getName() == $name) {
                return $child;
            }
        }
        return null;
    }

    /**
     * Finding element by tag name, and checking attributes with namespaces
     *
     * @param Varien_Simplexml_Element $source
     * @param string $namespace
     * @return null|Varien_Simplexml_Element
     */
    public function _getElementByName($source, $elmNamespace = '')
    {
        $sourceName = $source->getName();
        $extendElmAttributes = $this->getAttributes($source);
        foreach ($this->children($elmNamespace) as $k => $child) {
            if ($child->getName() == $sourceName) {
                $elm = true;
                foreach ($extendElmAttributes as $namespace => $attributes) {
                    /**
                     * if count of attributes of extend element is 0 in $namespace,
                     * and current element has attributes in $namespace - different elements
                     */
//                    if (!count($attributes) && count($this->getAttributes($child, $namespace))) {
//                        foreach ($this->getAttributes($child, $namespace) as $attribute) {
//                            $elm = false;
//                            break;
//                        }
//                    }
                    foreach ($attributes as $key => $value) {
                        if (is_null($child->getAttribute($key, $namespace)) || $child->getAttribute($key, $namespace) != $value) {
                            $elm = false;
                        }
                    }
                }
                /**
                 * if count of namespaces attributes of element to extend is 0,
                 * but current element has namespaces attributes - different elements
                 */
                if (!count($extendElmAttributes) && count($this->getAttributes($child))) {
                    $elm = false;
                }
                if ($elm) {
                    return $child;
                }
            }
        }
        return null;
    }

}
