<?php

class Mage_Sales_Model_Invoice_Payment extends Mage_Core_Model_Abstract
{
    function _construct()
    {
        $this->_init('sales/invoice_transaction');
    }
}