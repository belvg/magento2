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
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sitemap edit form container
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sitemap_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    /**
     * Init container
     */
    public function __construct()
    {
        $this->_objectId = 'sitemap_id';
        $this->_controller = 'sitemap';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('adminhtml')->__('Save Sitemap'));
        $this->_updateButton('delete', 'label', Mage::helper('adminhtml')->__('Delete Sitemap'));
        if (Mage::registry('sitemap_sitemap') && $sitemapId = Mage::registry('sitemap_sitemap')->getId()) {
            $generationUrl = Mage::helper('core')->htmlEscape(Mage::getUrl('*/*/generate', array('sitemap_id' => $sitemapId, 'go_back' => 1)));
            $this->_addButton('generate', array(
                'label'   => Mage::helper('adminhtml')->__('Generate'),
                'onclick' => "setLocation('{$generationUrl}')",
                'class'   => 'add',
            ));
        }
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('sitemap_sitemap')->getId()) {
            return Mage::helper('sitemap')->__('Edit Sitemap');
        }
        else {
            return Mage::helper('sitemap')->__('New Sitemap');
        }
    }

}
