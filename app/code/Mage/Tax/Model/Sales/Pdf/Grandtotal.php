<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Tax
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Tax_Model_Sales_Pdf_Grandtotal extends Mage_Sales_Model_Order_Pdf_Total_Default
{
    /**
     * Check if tax amount should be included to grandtotals block
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $store = $this->getOrder()->getStore();
        $config= Mage::getSingleton('Mage_Tax_Model_Config');
        if (!$config->displaySalesTaxWithGrandTotal($store)) {
            return parent::getTotalsForDisplay();
        }
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        $amountExclTax = $this->getAmount() - $this->getSource()->getTaxAmount();
        $amountExclTax = ($amountExclTax > 0) ? $amountExclTax : 0;
        $amountExclTax = $this->getOrder()->formatPriceTxt($amountExclTax);
        $tax = $this->getOrder()->formatPriceTxt($this->getSource()->getTaxAmount());
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

        $totals = array(array(
            'amount'    => $this->getAmountPrefix().$amountExclTax,
            'label'     => Mage::helper('Mage_Tax_Helper_Data')->__('Grand Total (Excl. Tax)') . ':',
            'font_size' => $fontSize
        ));

        if ($config->displaySalesFullSummary($store)) {
           $totals = array_merge($totals, $this->getFullTaxInfo());
        }

        $totals[] = array(
            'amount'    => $this->getAmountPrefix().$tax,
            'label'     => Mage::helper('Mage_Tax_Helper_Data')->__('Tax') . ':',
            'font_size' => $fontSize
        );
        $totals[] = array(
            'amount'    => $this->getAmountPrefix().$amount,
            'label'     => Mage::helper('Mage_Tax_Helper_Data')->__('Grand Total (Incl. Tax)') . ':',
            'font_size' => $fontSize
        );
        return $totals;
    }
}