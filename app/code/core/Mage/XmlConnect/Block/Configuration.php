<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Rss
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Application configuration renderer
 *
 * @category   Mage
 * @package    Mage_XmlConnect
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Mage_XmlConnect_Block_Configuration extends Mage_Core_Block_Template
{
    protected $_app;

    /**
     * Init current application
     *
     * @return Mage_XmlConnect_Block_Configuration
     */
    protected function _beforeToHtml()
    {
        $app = Mage::registry('current_app');
        if ($app) {
            $this->_app = $app;
        } else {
            $this->_app = Mage::getModel('xmlconnect/application');
            $this->_app->loadDefaultConfiguration();
        }
        return $this;
    }

    /**
     * Get configuration value for current application
     *
     * @param string $path
     * @return string
     */
    protected function _getConf($path) {
        $isImage = FALSE;
        if ((substr($path, -5) == '/icon') ||
            (substr($path, -4) == 'Icon') ||
            (substr($path, -5) == 'Image')) {
            $isImage = TRUE;
        }
        if( $isImage && !empty($this->_app['conf/' . $path]) ) {
            $url = $this->_app['conf/' . $path];
            if (strpos($url, '://') === FALSE ) {
                $url = Mage::getBaseUrl('media') . 'xmlconnect/' . $url;
            }
            return $url;
        }
        return $this->_app['conf/' . $path];
    }

    /**
     * Recursively build XML configuration tree
     *
     * @param Varien_Simplexml_Element $section
     * @param array $subtree
     * @param string $prefix
     * @return Varien_Simplexml_Element
     */
    protected function _buildRecursive($section, $subtree, $prefix = '')
    {
        foreach ($subtree as $key => $value) {
            if (is_array($value)) {
                if (strtolower(substr($key, -4)) == 'font') {
                    $name = $section->xmlentities(trim($this->_getConf($prefix . $key . '/name')));
                    $size = $section->xmlentities(trim($this->_getConf($prefix . $key . '/size')));
                    $color = $section->xmlentities(trim($this->_getConf($prefix . $key . '/color')));
                    if (empty($name) || empty($size) || empty($color)) {
                        continue;
                    }
                    $font = $section->addChild($key);
                    $font->addAttribute('name', $name);
                    $font->addAttribute('size', $size);
                    $font->addAttribute('color', $color);
                }
                else {
                    $subsection = $section->addChild($key);
                    $this->_buildRecursive($subsection, $value, $prefix . $key . '/');
                }
            } else {
                $conf = $this->_getConf($prefix . $key);
                if (!empty($conf)) {
                    $section->addChild($key, $conf);
                }
            }
        }
    }

    /**
     * Render block
     *
     * @return string
     */
    protected function _toHtml()
    {
        $conf = Mage::getStoreConfig('defaultConfiguration');
        $xml = new Varien_Simplexml_Element('<configuration></configuration>');
        $xml->addChild('updateTimeUTC', strtotime($this->_app->getUpdatedAt()));
        $this->_buildRecursive($xml, $conf);
        return $xml->asNiceXml();
    }
}
