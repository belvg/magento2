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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales Order Creditmemo Pdf default items renderer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Pdf_Items_Creditmemo_Default extends Mage_Sales_Model_Order_Pdf_Items_Abstract
{
    public function draw()
    {
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();
        $lines  = array();

        $leftBound  =  35;
        $rightBound = 565;

        $x = $leftBound;
        // draw Product name
        $lines[0] = array(array(
            'text' => Mage::helper('Mage_Core_Helper_String')->str_split($item->getName(), 60, true, true),
            'feed' => $x,
        ));

        $x += 220;
        // draw SKU
        $lines[0][] = array(
            'text'  => Mage::helper('Mage_Core_Helper_String')->str_split($this->getSku($item), 25),
            'feed'  => $x
        );

        $x += 100;
        // draw Total (ex)
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getRowTotal()),
            'feed'  => $x,
            'font'  => 'bold',
            'align' => 'right',
            'width' => 50,
        );

        $x += 50;
        // draw Discount
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt(-$item->getDiscountAmount()),
            'feed'  => $x,
            'font'  => 'bold',
            'align' => 'right',
            'width' => 50,
        );

        $x += 50;
        // draw QTY
        $lines[0][] = array(
            'text'  => $item->getQty()*1,
            'feed'  => $x,
            'font'  => 'bold',
            'align' => 'center',
            'width' => 30,
        );

        $x += 30;
        // draw Tax
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getTaxAmount()),
            'feed'  => $x,
            'font'  => 'bold',
            'align' => 'right',
            'width' => 45,
        );

        $x += 45;
        // draw Subtotal
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getRowTotal() + $item->getTaxAmount() - $item->getDiscountAmount()),
            'feed'  => $rightBound,
            'font'  => 'bold',
            'align' => 'right'
        );

        // draw options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = array(
                    'text' => Mage::helper('Mage_Core_Helper_String')->str_split(strip_tags($option['label']), 70, true, true),
                    'font' => 'italic',
                    'feed' => $leftBound
                );

                // draw options value
                $_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
                $lines[][] = array(
                    'text' => Mage::helper('Mage_Core_Helper_String')->str_split($_printValue, 50, true, true),
                    'feed' => $leftBound + 5
                );
            }
        }

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 10
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }
}
