<?php
/**
 * Form fieldset
 *
 * @package    Ecom
 * @subpackage Data
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 */
class Varien_Data_Form_Element_Fieldset extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('fieldset');
    }

    public function getElementHtml()
    {
        $html = '<fieldset id="'.$this->getHtmlId().'"'.$this->serialize(array('class')).'>'."\n";
        if ($this->getLegend()) {
            $html.= '<legend>'.$this->getLegend().'</legend>'."\n";
        }
        foreach ($this->getElements() as $element) {
        	$html.= $element->toHtml();
        }
        $html.= '</fieldset></div>'."\n";
        return $html;
    }

    public function getDefaultHtml()
    {
        $html = '<div><h4 class="icon-head head-edit-form fieldset-legend">'.$this->getLegend().'</h4>'."\n";
        $html.= $this->getElementHtml();
        return $html;
    }
}