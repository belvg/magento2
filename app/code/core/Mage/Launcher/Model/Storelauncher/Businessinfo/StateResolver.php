<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Launcher
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * State resolver for BusinessInfo Tile
 *
 * @category   Mage
 * @package    Mage_Launcher
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Launcher_Model_Storelauncher_Businessinfo_StateResolver implements Mage_Launcher_Model_Tile_StateResolver
{
    /**
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * Constructor
     *
     * @param Mage_Core_Model_App $app
     * @param Mage_Core_Model_Config $config
     */
    function __construct(
        Mage_Core_Model_App $app,
        Mage_Core_Model_Config $config
    ) {
        $this->_app = $app;
        $this->_config = $config;
    }

    /**
     * Resolve state
     *
     * @return bool
     */
    public function isTileComplete()
    {
        $this->_config->reinit();
        $email = $this->_app->getStore()->getConfig('trans_email/ident_general/email');
        return isset($email) && ($email != 'owner@example.com');
    }

    /**
     * Handle System Configuration change (handle related event) and return new state
     *
     * @param string $sectionName
     * @param int $currentState current state of the tile
     * @return int result state
     */
    public function handleSystemConfigChange($sectionName, $currentState)
    {
        if (in_array($sectionName, array('trans_email'))) {
            if ($this->isTileComplete()) {
                return Mage_Launcher_Model_Tile::STATE_COMPLETE;
            }
            if ($currentState == Mage_Launcher_Model_Tile::STATE_COMPLETE) {
                return Mage_Launcher_Model_Tile::STATE_TODO;
            }
        }
        return $currentState;
    }

    /**
     * Get Persistent State of the Tile
     *
     * @return int
     */
    public function getPersistentState()
    {
        if ($this->isTileComplete()) {
            return Mage_Launcher_Model_Tile::STATE_COMPLETE;
        }
        return Mage_Launcher_Model_Tile::STATE_TODO;
    }
}
