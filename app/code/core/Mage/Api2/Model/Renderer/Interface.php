<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Api2
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Webservice API2 renderer adapter interface
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
interface Mage_Api2_Model_Renderer_Interface
{
    /**
     * Render content in a certain format
     *
     * @param array|object $data
     * @return string
     */
    public function render($data);

    /**
     * Get MIME type generated by renderer
     *
     * @return string
     */
    public function getMimeType();
}