<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_GiftMessage
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_GiftMessage_Block_Message_InlineTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_GiftMessage_Block_Message_Inline
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = new Mage_GiftMessage_Block_Message_Inline();
    }

    protected function tearDown()
    {
        $this->_block = null;
    }

    /**
     * magentoDataFixture Mage/Catalog/_files/product_with_image.php
     */
    public function testThumbnail()
    {
        $this->markTestIncomplete('Need to fix DI dependencies + fixture');

        $product = new Mage_Catalog_Model_Product();
        $product->load(1);

        $size = $this->_block->getThumbnailSize();
        $this->assertGreaterThan(1, $size);
        $this->assertContains('/'.$size, $this->_block->getThumbnailUrl($product));
        $this->assertStringEndsWith('magento_image.jpg', $this->_block->getThumbnailUrl($product));
    }
}
