<?php
/**
 * Form select element
 *
 * @package    Varien
 * @subpackage Form
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 */
class Varien_Data_Form_Element_Multiselect extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('select');
        $this->setExtType('combobox');
    }

    public function getElementHtml()
    {
        $this->addClass('select');
        $html = '<select id="'.$this->getHtmlId().'" name="'.$this->getName().'" '.$this->serialize($this->getHtmlAttributes()).' multiple="multiple">'."\n";

        $value = $this->getValue();
        if (!is_array($value)) {
            $value = array($value);
        }

        if ($values = $this->getValues()) {
            foreach ($values as $option) {
                if (is_array($option['value'])) {
                    $html.='<optgroup label="'.$optionInfo['label'].'">'."\n";
                    foreach ($optionInfo['value'] as $groupItem) {
                        $html.= $this->_optionToHtml($groupItem, $value);
                    }
                    $html.='</optgroup>'."\n";
                }
                else {
                    $html.= $this->_optionToHtml($option, $value);
                }
            }
        }

        $html.= '</select>'."\n";
        return $html;
    }
    
    public function getHtmlAttributes()
    {
        return array('title', 'class', 'style', 'onclick', 'onchange', 'disabled', 'size');
    }
    
    public function getDefaultHtml()
    {
    	$result = ( $this->getNoSpan() === true ) ? '' : '<span class="field-row">'."\n";
        $result.= $this->getLabelHtml();
        $result.= $this->getElementHtml();
        
        
        if($this->getSelectAll() && $this->getDeselectAll()) {
    		$result.= '<a href="#" onclick="return ' . $this->getJsObjectName() . '.selectAll()">' . $this->getSelectAll() . '</a> <span class="separator">&nbsp;|&nbsp;</span>';
    		$result.= '<a href="#" onclick="return ' . $this->getJsObjectName() . '.deselectAll()">' . $this->getDeselectAll() . '</a>';
    	}
    	
        $result.= ( $this->getNoSpan() === true ) ? '' : '</span>'."\n";
    	   	
    	
    	$result.= '<script type="text/javascript">' . "\n";
    	$result.= '   var ' . $this->getJsObjectName() . ' = {' . "\n";
    	$result.= '   	selectAll: function() { ' . "\n";
    	$result.= '   		var sel = $("' . $this->getHtmlId() . '");' . "\n";
    	$result.= '   		for(var i = 0; i < sel.options.length; i ++) { ' . "\n";
    	$result.= '   			sel.options[i].selected = true; ' . "\n";
    	$result.= '   		} ' . "\n";
    	$result.= '   		return false; ' . "\n";
    	$result.= '   	},' . "\n";
    	$result.= '   	deselectAll: function() {' . "\n";
		$result.= '   		var sel = $("' . $this->getHtmlId() . '");' . "\n";
		$result.= '   		for(var i = 0; i < sel.options.length; i ++) { ' . "\n";
    	$result.= '   			sel.options[i].selected = false; ' . "\n";
    	$result.= '   		} ' . "\n";
    	$result.= '   		return false; ' . "\n";
    	$result.= '   	}' . "\n";
    	$result.= '  }' . "\n";
    	$result.= "\n</script>";
    	
    	return $result;
    }
    
    public function getJsObjectName() {
    	 return $this->getHtmlId() . 'ElementControl';
    }
    
    protected function _optionToHtml($option, $selected)
    {
        $id = $this->getHtmlId().'.'.$this->_escape($option['value']);
        $html = '<li><input type="checkbox" id="'.$id.'" name="'.$this->getName().'" class="input-checkbox" value="'.$this->_escape($option['value']).'"';
        $html.= isset($option['title']) ? 'title="'.$option['title'].'"' : '';
        $html.= isset($option['style']) ? 'style="'.$option['style'].'"' : '';
        if (in_array($option['value'], $selected)) {
            $html.= ' checked="selected"';
        }
        $html.= '/><label for="'.$id.'">'.$option['label']. '</label></li>'."\n";
        return $html;
    }
}