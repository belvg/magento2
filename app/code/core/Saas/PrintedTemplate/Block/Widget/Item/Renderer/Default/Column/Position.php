<?php
/**
 * {license_notice}
 *
 * @category    Saas
 * @package     Saas_PrintedTemplate
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Base renderer for "position" field
 *
 * @category    Saas
 * @package     Saas_PrintedTemplate
 * @subpackage  Blocks
 */
class Saas_PrintedTemplate_Block_Widget_Item_Renderer_Default_Column_Position
    extends Mage_Backend_Block_Abstract
    implements Saas_PrintedTemplate_Block_Widget_Item_Renderer_Default_Column_Abstract
{
    /**
     * Current (last) position numbers for specified widget
     *
     * @var array
     */
    static protected $_currentPositionByWidget = array();

    /**
     * Position numbers for each order item
     *
     * @var array
     */
    static protected $_itemPositionsByWidget = array();

    /**
     * Build item name HTML
     *
     * @return string
     */
    public function getHtml()
    {
        $currentPosition = $this->_getCurrentPosition();

        return $currentPosition ? $currentPosition : '';
    }

    /**
     * Get current posiotion of current item in the current widget
     *
     * @return int|bool Return 'false' if widget isn't found, otherwise return position
     */
    protected function _getCurrentPosition()
    {
        // check if item is main (not child of complex product) and widget is avaliable
        $parentBlock = $this->getParentBlock();
        if ($this->getItem()->getOrderItem()->getParentItemId() ||
            !$parentBlock || !$parentBlock->getParentBlock() ||
            !$parentBlock->getParentBlock() instanceof Saas_PrintedTemplate_Block_Widget_AbstractGrid) {
            return false;
        }

        $itemId = $this->getItem()->getOrderItem()->getId();
        $widgetId = $this->getParentBlock()->getParentBlock()->getWidgetId();
        // cache position number for each order item
        if (isset(self::$_itemPositionsByWidget[$widgetId][$itemId])) {
            return self::$_itemPositionsByWidget[$widgetId][$itemId];
        } elseif (!isset(self::$_itemPositionsByWidget[$widgetId])) {
            self::$_itemPositionsByWidget[$widgetId] = array();
        }

        // calculate next position number
        if (!isset(self::$_currentPositionByWidget[$widgetId])) {
            self::$_currentPositionByWidget[$widgetId] = 0;
        }

        self::$_itemPositionsByWidget[$widgetId][$itemId] = ++self::$_currentPositionByWidget[$widgetId];

        return self::$_itemPositionsByWidget[$widgetId][$itemId];
    }
}