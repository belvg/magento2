<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category    Enterprise
 * @package     Enterprise_Logging
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Difference columns renderer
 *
 */
class Enterprise_Logging_Block_Adminhtml_Details_Renderer_Diff
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Render the grid cell value
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $data = unserialize($row->getData($this->getColumn()->getIndex()));

        $html = '';
        $specialFlag = false;
        if ($data !== false) {
            if (isset($data['__no_changes'])) {
                $html = $this->__('No changes');
                $specialFlag = true;
            }
            if (isset($data['__was_deleted'])) {
                $html = $this->__('Item was deleted');
                $specialFlag = true;
            }
            if (isset($data['__was_created'])) {
                $html = $this->__('N/A');
                $specialFlag = true;
            }
            $data = (array)$data;
            if (!$specialFlag) {
                $html = '<dl>';
                foreach ($data as $key => $value) {
                    $html .= '<dt>' . $key . '</dt><dd>' . $this->htmlEscape($value) . '</dd>';
                }
                $html .= '</dl>';
            }
        }
        return $html;
    }
}
