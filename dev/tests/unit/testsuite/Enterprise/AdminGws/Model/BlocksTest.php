<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Enterprise_AdminGws
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Enterprise_AdminGws_Model_BlocksTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Enterprise_AdminGws_Model_Blocks
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Enterprise_AdminGws_Model_Blocks(array('role' => new Varien_Object()));
    }

    public function testDisableTaxRelatedMultiselects()
    {
        $form = new Varien_Data_Form();
        $element1 = new Varien_Data_Form_Element_Editablemultiselect();
        $element1->setId('tax_customer_class');
        $element2 = new Varien_Data_Form_Element_Editablemultiselect();
        $element2->setId('tax_product_class');
        $element3 = new Varien_Data_Form_Element_Editablemultiselect();
        $element3->setId('tax_rate');
        $form->addElement($element1);
        $form->addElement($element2);
        $form->addElement($element3);
        $observerMock = new Varien_Object(array(
            'event' => new Varien_Object(array(
                'block' => new Varien_Object(array('form' => $form))
            ))
        ));

        $this->_model->disableTaxRelatedMultiselects($observerMock);

        $this->assertTrue($form->getElement('tax_product_class')->getDisabled());
        $this->assertTrue($form->getElement('tax_customer_class')->getDisabled());
        $this->assertTrue($form->getElement('tax_rate')->getDisabled());
    }
}