<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Saas_Launcher
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * State resolver for Googleanalytics Tile
 *
 * @category   Mage
 * @package    Saas_Launcher
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Saas_Launcher_Model_Promotestore_Googleanalytics_StateResolver
    extends Saas_Launcher_Model_Tile_ConfigBased_StateResolverAbstract
{
    /**
     * Constructor
     *
     * @param Mage_Core_Model_App $app
     */
    public function __construct(Mage_Core_Model_App $app)
    {
        parent::__construct($app);
        $this->_sections = array('google');
    }

    /**
     * Resolve state
     *
     * @return bool
     */
    public function isTileComplete()
    {
        $account = $this->_app->getStore()->getConfig('google/analytics/account');
        return !empty($account);
    }
}