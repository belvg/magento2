<?php

class Mage_Rule_Model_Action_Collection extends Mage_Rule_Model_Action_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setActions(array());
    }
    
    /**
     * Returns array containing actions in the collection
     * 
     * Output example:
     * array(
     *   {action::asArray},
     *   {action::asArray}
     * )
     * 
     * @return array
     */
    public function asArray(array $arrAttributes = array())
    {
        $out = array();
        
        foreach ($this->getActions() as $item) {
            $out[] = $item->asArray();
        }
        
        return $out;
    }
    
    public function loadArray(array $arr)
    {
        $salesConfig = Mage::getSingleton('sales/config');
        
        foreach ($arr as $actArr) {
            $action = $this->getRule()->getActionInstance($actArr['type']);
            $action->loadArray($actArr);
            $this->addAction($action);
        }
        return $this;
    }
    
    public function addAction(Mage_Rule_Model_Action_Interface $action)
    {
        $actions = $this->getActions();
        
        $action->setRule($this->getRule());

        $actions[] = $action;
        if (!$action->getId()) {
            $action->setId($this->getId().'.'.sizeof($actions));
        }
        
        $this->setActions($actions);
        return $this;
    }
    
    public function asHtml()
    {    	
        $form = $this->getRule()->getForm();
        $newChildEl = $form->addField('action:'.$this->getId().':new_child', 'select', array(
    		'name'=>'rule[actions]['.$this->getId().'][new_child]',
    		'values'=>$this->getNewChildSelectOptions(),
    		'value_name'=>$this->getNewChildName(),
    	))->setRenderer(new Mage_Rule_Block_Newchild());
    	
    	$html = 'Perform following actions:';
    	$html.= '('.$newChildEl->getHtml().'):';
        return $html;	
    }
    
    public function asHtmlRecursive()
    {
        $html = $this->asHtml().'<ul id="action:'.$this->getId().':children">';
        foreach ($this->getActions() as $cond) {
            $html .= $cond->asHtmlRecursive();
        }
        $html .= '</ul>';
        return $html;
    }
    
    public function asString($format='')
    {
        $str = "Perform following actions";
        return $str;
    }
    
    public function asStringRecursive($level=0)
    {
        $str = $this->asString();
        foreach ($this->getActions() as $action) {
            $str .= "\n".$action->asStringRecursive($level+1);
        }
        return $str;
    }
    
    public function process()
    {
        foreach ($this->getActions() as $action) {
            $action->process();
        }
        return $this;
    }
}