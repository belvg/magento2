<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Review
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Review_Helper_Action_PagerTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Review_Helper_Action_Pager */
    protected $_helper = null;

    /**
     * Prepare helper object
     */
    protected function setUp()
    {
        $sessionMock = $this->getMockBuilder('Mage_Backend_Model_Session')
            ->disableOriginalConstructor()
            ->setMethods(array('setData', 'getData'))
            ->getMock();
        $sessionMock->expects($this->any())
            ->method('setData')
            ->with($this->equalTo('search_result_idsreviews'), $this->anything());
        $sessionMock->expects($this->any())
            ->method('getData')
            ->with($this->equalTo('search_result_idsreviews'))
            ->will($this->returnValue(array(3,2,6,5)));

        $this->_helper = $this->getMockBuilder('Mage_Review_Helper_Action_Pager')
            ->setMethods(array('_getSession'))
            ->getMock();
        $this->_helper->expects($this->any())
            ->method('_getSession')
            ->will($this->returnValue($sessionMock));
        $this->_helper->setStorageId('reviews');
    }

    /**
     * Test storage set with proper parameters
     */
    public function testStorageSet()
    {
        $this->_helper->setItems(array(1));
    }

    /**
     * Test getNextItem
     */
    public function testGetNextItem()
    {
        $this->assertEquals(2, $this->_helper->getNextItemId(3));
    }

    /**
     * Test getNextItem when item not found or no next item
     */
    public function testGetNextItemNotFound()
    {
        $this->assertFalse($this->_helper->getNextItemId(30));
        $this->assertFalse($this->_helper->getNextItemId(5));
    }

    /**
     * Test getPreviousItemId
     */
    public function testGetPreviousItem()
    {
        $this->assertEquals(2, $this->_helper->getPreviousItemId(6));
    }

    /**
     * Test getPreviousItemId when item not found or no next item
     */
    public function testGetPreviousItemNotFound()
    {
        $this->assertFalse($this->_helper->getPreviousItemId(30));
        $this->assertFalse($this->_helper->getPreviousItemId(3));
    }
}