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
 * Printed templates data helper
 *
 * @category   Saas
 * @package    Saas_PrintedTemplate
 * @subpackage Helpers
 */
class Saas_PrintedTemplate_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @var Mage_Backend_Model_Menu_Config
     */
    protected $_menuConfig;

    /**
     * @var Mage_Backend_Model_Config_Structure
     */
    protected $_configStructure;

    public function __construct()
    {
        $this->_menuConfig = Mage::getSingleton('Mage_Backend_Model_Menu_Config');
        $this->_configStructure = Mage::getSingleton('Mage_Backend_Model_Config_Structure');
    }


    /**
     * Get onclick script for Print button on Invoice View page
     *
     * @param string $type
     * @return string
     */
    public function getPrintButtonOnclick($type)
    {
        return "setLocation('" . $this->_getPrintUrl($type) . "')";
    }

    /**
     * Get print URL for specified entity type
     *
     * @param string $type
     * @return string
     */
    protected function _getPrintUrl($type)
    {
        $model = Mage::registry('current_' . $type);
        if (!$model || !$model->getId()) {
            return '';
        }

        return $this->helper('Mage_Backend_Helper_Data')->getUrl(
            'adminhtml/print/entity/',
            array(
                'type' => $type,
                'id'   => $model->getId(),
            )
        );
    }

    /**
     * Return helper object
     *
     * @param string $className
     * @return Mage_Core_Helper_Abstract
     */
    public function helper($className)
    {
        return Mage::helper($className);
    }

    /**
     * Convert xml config pathes to decorated names
     *
     * @param array $paths
     * @return array
     */
    public function getSystemConfigPathsParts($paths)
    {
        $result = $urlParams = $prefixParts = array();
        $scopeLabel = $this->helper('Mage_Backend_Helper_Data')->__('GLOBAL');
        if ($paths) {
            /**
             * @todo check functionality of getting Mage_Backend_Model_Menu_Config object
             */
            /** @var $menu Mage_Backend_Model_Menu */
            $menu = $this->_menuConfig->getMenu();
            $item = $menu->get('Mage_Adminhtml::system');
            // create prefix path parts
            $prefixParts[] = array(
                'title' => $item->getModuleHelper()->__($item->getTitle()),
            );
            $item = $menu->get('Mage_Adminhtml::system_config');
            $prefixParts[] = array(
                'title' => $item->getModuleHelper()->__($item->getTitle()),
                'url' => $this->helper('Mage_Backend_Helper_Data')->getUrl('adminhtml/system_config/'),
            );

            $pathParts = $prefixParts;
            foreach ($paths as $pathData) {
                $pathDataParts = explode('/', $pathData['path']);
                $sectionName = array_shift($pathDataParts);

                $urlParams = array('section' => $sectionName);
                if (isset($pathData['scope']) && isset($pathData['scope_id'])) {
                    switch ($pathData['scope']) {
                        case 'stores':
                            $store = Mage::app()->getStore($pathData['scope_id']);
                            if ($store) {
                                $urlParams['website'] = $store->getWebsite()->getCode();
                                $urlParams['store'] = $store->getCode();
                                $scopeLabel = $store->getWebsite()->getName() . '/' . $store->getName();
                            }
                            break;
                        case 'websites':
                            $website = Mage::app()->getWebsite($pathData['scope_id']);
                            if ($website) {
                                $urlParams['website'] = $website->getCode();
                                $scopeLabel = $website->getName();
                            }
                            break;
                        default:
                            break;
                    }
                }
                /**
                 * @todo check functionality of getting Mage_Backend_Model_Config_Structure object
                 */
                $pathParts[] = array(
                    'title' => $this->_configStructure->getElement($sectionName)->getLabel(),
                    'url' => $this->helper('Mage_Backend_Helper_Data')
                        ->getUrl('adminhtml/system_config/edit', $urlParams),
                );
                $elementPathParts = array($sectionName);
                while (count($pathDataParts) != 1) {
                    $elementPathParts[] = array_shift($pathDataParts);
                    $pathParts[] = array(
                        'title' => $this->_configStructure
                            ->getElementByPathParts($elementPathParts)
                            ->getLabel()
                    );
                }
                $elementPathParts[] = array_shift($pathDataParts);
                $pathParts[] = array(
                    'title' => $this->_configStructure
                        ->getElementByPathParts($elementPathParts)
                        ->getLabel(),
                    'scope' => $scopeLabel
                );
                $result[] = $pathParts;
                $pathParts = $prefixParts;
            }
        }
        return $result;
    }
}
