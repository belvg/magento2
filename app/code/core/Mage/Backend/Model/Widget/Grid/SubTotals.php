<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Backend_Model_Widget_Grid_SubTotals extends Mage_Backend_Model_Widget_Grid_Totals_Abstract
{
    /**
     * Count collection column sum based on column index
     *
     * @param $index
     * @param $collection
     * @return float|int
     */
    protected function _countSum($index, $collection)
    {
        $sum = 0;
        foreach ($collection as $item) {
            $sum += $item[$index];
        }
        return $sum;
    }

    /**
     * Count collection column average based on column index
     *
     * @param $index
     * @param $collection
     * @return float|int
     */
    protected function _countAverage($index, $collection)
    {
        $numItems = count($collection);
        return ($numItems)? $this->_countSum($index, $collection) / $numItems : $numItems;
    }
}
