<?php
/**
 * Create product settings tab
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Settings extends Mage_Adminhtml_Block_Widget_Form 
{
    protected function _initChildren()
    {
        $this->setChild('continue_button', 
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => __('Continue'),
                    'onclick'   => "setSettings('".$this->getContinueUrl()."','attribute_set_id','product_type')",
                    'class'     => 'save'
					))
				);
    }
    
    protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$fieldset = $form->addFieldset('settings', array('legend'=>__('Create Product Settings')));
		
		$entityType = Mage::registry('product')->getResource()->getConfig();

		$fieldset->addField('attribute_set_id', 'select', array(
            'label' => __('Attribute Set'),
            'title' => __('Attribute Set'),
            'name'  => 'set',
            'value' => $entityType->getDefaultAttributeSetId(),
            'values'=> Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter($entityType->getId())
                ->load()
                ->toOptionArray()
		));
		
		$fieldset->addField('product_type', 'select', array(
            'label' => __('Product Type'),
            'title' => __('Product Type'),
            'name'  => 'type',
            'value' => '',
            'values'=> Mage::getResourceModel('catalog/product_type_collection')
                ->load()
                ->toOptionArray()
		));
		
		$fieldset->addField('continue_button', 'note', array(
            'text' => $this->getChildHtml('continue_button'),
		));
		
		$this->setForm($form);
	}
	
	public function getContinueUrl()
	{
	    return Mage::getUrl('*/*/new', array('_current'=>true));
	}
}
