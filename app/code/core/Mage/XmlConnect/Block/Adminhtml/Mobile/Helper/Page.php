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
 * @category    Mage
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_XmlConnect_Block_Adminhtml_Mobile_Helper_Page extends Varien_Data_Form_Element_Abstract
{
    /**
     * Enter description here...
     *
     * @param array $attributes
     */
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('page');
    }


    public function initFields($conf)
    {
        $this->addElement(new Varien_Data_Form_Element_Text(array(
            'name'      => $conf['name'] . '[label]',
        )));

        $this->addElement(new Varien_Data_Form_Element_Select(array(
            'name'      => $conf['name'] . '[id]',
            'values'    => $conf['values'],
        )));
    }

    /**
     * Add form element
     *
     * @param   Varien_Data_Form_Element_Abstract $element
     * @return  Varien_Data_Form
     */
    public function addElement(Varien_Data_Form_Element_Abstract $element, $after=false)
    {
        $element->setId($element->getData('name'));
        parent::addElement($element, $after);
    }

    /**
     * Enter description here...
     *
     * @param string
     */
    public function getLabelHtml($idSuffix = '')
    {
        list($label, $element) = $this->getElements();
        return $label->toHtml();
    }

    /**
     * Enter description here...
     *
     * @param string
     */
    public function getElementHtml()
    {
        list($label, $element) = $this->getElements();
        return $element->toHtml();
    }
}
