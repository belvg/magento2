<?php
/**
 * Interface of REST response renderers.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
interface Mage_Webapi_Controller_Response_Rest_RendererInterface
{
    /**
     * Render content in a certain format.
     *
     * @param array|object $data
     * @return string
     */
    public function render($data);

    /**
     * Get MIME type generated by renderer.
     *
     * @return string
     */
    public function getMimeType();
}
