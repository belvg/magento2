<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Menu filter iterator
 */
class Mage_Backend_Model_Menu_Filter_Iterator extends FilterIterator
{
    /**
     * Check whether the current element of the iterator is acceptable
     *
     * @return bool true if the current element is acceptable, otherwise false.
     */
    public function accept()
    {
        return !($this->current()->isDisabled() || !($this->current()->isAllowed()));
    }
}
