<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Catalog
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Catalog_Block_Product_View_AdditionalTest extends PHPUnit_Framework_TestCase
{
    public function testGetChildHtmlList()
    {
        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getModel('Mage_Core_Model_Layout');
        /** @var $block Mage_Catalog_Block_Product_View_Additional */
        $block = $layout->createBlock('Mage_Catalog_Block_Product_View_Additional', 'block');

        /** @var $childFirst Mage_Core_Block_Text */
        $childFirst = $layout->addBlock('Mage_Core_Block_Text', 'child1', 'block');
        $htmlFirst = '<b>Any html of child1</b>';
        $childFirst->setText($htmlFirst);

        /** @var $childSecond Mage_Core_Block_Text */
        $childSecond = $layout->addBlock('Mage_Core_Block_Text', 'child2', 'block');
        $htmlSecond = '<b>Any html of child2</b>';
        $childSecond->setText($htmlSecond);

        $list = $block->getChildHtmlList();

        $this->assertInternalType('array', $list);
        $this->assertCount(2, $list);
        $this->assertContains($htmlFirst, $list);
        $this->assertContains($htmlSecond, $list);
    }
}
