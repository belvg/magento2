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
 * @category   Enterprise
 * @package    Enterprise_Staging
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Staging config model
 */
class Enterprise_Staging_Model_Staging_Config
{
    /**
     * Staging statuses
     */
    const STATUS_PROCESSING     = 'processing';
    const STATUS_COMPLETE       = 'complete';
    const STATUS_CREATED        = 'created';
    const STATUS_UPDATED        = 'updated';
    const STATUS_BACKUP_CREATED = 'backup_created';
    const STATUS_MERGED         = 'merged';
    const STATUS_RESTORED       = 'restored';
    const STATUS_HOLDED         = 'holded';
    const STATUS_FAIL           = 'fail';

    /**
     * Staging visibility codes
     */
    const VISIBILITY_NOT_ACCESSIBLE     = 'not_accessible';
    const VISIBILITY_ACCESSIBLE         = 'accessible';
    const VISIBILITY_REQUIRE_HTTP_AUTH  = 'require_http_auth';

    /**
     * Retrieve staging module xml config as Varien_Simplexml_Element object
     *
     * @param   string $path
     * @return  object Varien_Simplexml_Element
     */
    static public function getConfig($path = null)
    {
        $_path = 'global/enterprise/staging/';
        if (!is_null($path)) {
            $_path .= ltrim($path, '/');
        }
        return Mage::getConfig()->getNode($_path);
    }

    /**
     * Get Config node as mixed option array
     *
     * @param string $nodeName
     * @return mixed
     */
    static public function getOptionArray($nodeName)
    {
        $options = array();
        $config = self::getConfig($nodeName);
        if ($config) {
            foreach ($config->children() as $node) {
                $label = Mage::helper('enterprise_staging')->__((string)$node->label);
                $options[$node->getName()] = $label;
            }
        }
        return $options;
    }

    public function getVisibilityOptionArray()
    {
        return array(
            'not_accessible'    => Mage::helper('enterprise_staging')->__('Not accessible'),
            'accessible'        => Mage::helper('enterprise_staging')->__('Accessible'),
            'require_http_auth' => Mage::helper('enterprise_staging')->__('Require Http Auth')
        );
    }

    /**
     * Get Config node as mixed option array, with selected structure: value, label
     *
     * @param string $nodeName
     * @return mixed
     */
    static public function getAllOptions($nodeName)
    {
        $res = array();
        foreach (self::getOptionArray($nodeName) as $value => $label) {
            $res[] = array(
               'value' => $value,
               'label' => $label
            );
        }
        return $res;
    }

    /**
     * Get Config node as mixed option array, with selected structure: value, label
     * If $addEmpty true - add empty option
     *
     * @param string $nodeName
     * @param boolean $addEmpty
     * @return array
     */
    static public function toOptionArray($nodeName, $addEmpty = false)
    {
        $result = array();
        if ($addEmpty) {
            $result[] = array('value' => '','label' => '');
        }
        foreach (self::getOptionArray($nodeName) as $value => $label) {
            $result[] = array('value' => $value,'label' => $label);
        }
        return $result;
    }

    /**
     * get Config node as text by option id
     *
     * @param mixed  $optionId
     * @param string $nodeName
     * @return text
     */
    static public function getOptionText($optionId, $nodeName)
    {
        $options = self::getOptionArray($nodeName);
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * Retrieve Staging Items
     *
     * @return mixed
     */
    public function getStagingItems()
    {
        $stagingItems = self::getConfig('staging_items');
        if ($stagingItems) {
            return $stagingItems->children();
        }
        return array();
    }

    /**
     * Retrieve staging item by item code
     *
     * @param  string $itemCode
     * @return string
     */
    public function getStagingItem($itemCode)
    {
        $stagingItems = $this->getStagingItems();
        if (!empty($stagingItems->{$itemCode})) {
            return $stagingItems->{$itemCode};
        } else {
            foreach ($stagingItems as $stagingItem) {
                if ($stagingItem->extends) {
                    if ($stagingItem->extends->{$itemCode}) {
                        return $stagingItem->extends->{$itemCode};
                    }
                }
            }
            return null;
        }
    }

    /**
     * Check if module given item is active
     *
     * @param  Varien_Simplexml_Element $stagingItem
     * @return boolean
     */
    function isItemModuleActive($stagingItem)
    {
        $moduleName = (string) $stagingItem->module;
        if (!empty($moduleName)) {
            $module = Mage::getConfig()->getModuleConfig($moduleName);
            if ($module) {
                if ($module->is('active')) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Retrieve Staging Action Label
     *
     * @param   string $process
     * @return  string
     */
    static public function getStagingProcessLabel($process)
    {
        $processNode = self::getConfig('processes/'.$process);
        if ($processNode) {
            $process = (string) $processNode->label;
            return Mage::helper('enterprise_staging')->__($process);
        }
        return $process;
    }

    /**
     * Retrieve status label
     *
     * @param   string $status
     * @return  string
     */
    static public function getStatusLabel($status)
    {
        $statusNode = self::getConfig('status/'.$status);
        if ($statusNode) {
            $status = (string) $statusNode->label;
            return Mage::helper('enterprise_staging')->__($status);
        }
        return $status;
    }

    /**
     * Retrieve visibility label
     *
     * @param   string $visibility
     * @return  string
     */
    static public function getVisibilityLabel($visibility)
    {
        $labels = $this->getVisibilityOptionArray();
        return isset($labels[$visibility]) ? $labels[$visibility] : null;
    }

    /**
     * Retrieve staging table prefix
     *
     * @param   Enterprise_Staging_Model_Staging $staging
     * @param   string $internalPrefix
     * @return  string
     */
    public function getTablePrefix($staging = null, $internalPrefix = '')
    {
        $globalTablePrefix  = (string) Mage::getConfig()->getTablePrefix();
        $stagingTablePrefix = $this->getStagingTablePrefix();

        if (!is_null($staging)) {
            $stagingTablePrefix = $staging->getTablePrefix();
        } else {
            $stagingTablePrefix = $globalTablePrefix . $stagingTablePrefix;
        }
        $stagingTablePrefix .= $internalPrefix;

        return $stagingTablePrefix;
    }

    /**
     * Get staging global table prefix
     *
     * @return string
     */
    public function getStagingTablePrefix()
    {
        return (string) self::getConfig('global_staging_table_prefix');
    }

    /**
     * Get staging global table prefix
     *
     * @return string
     */
    public function getStagingBackupTablePrefix()
    {
        return (string) self::getConfig('global_staging_backup_table_prefix');
    }

    /**
     * Get staging backend table name (for frontend usage)
     *
     * @param string $tableName
     * @param string $modelEntity
     * @param object Mage_Core_Model_Website $stagingWebsite
     *
     * @return false | string
     */
    public function getStagingFrontendTableName($tableName, $modelEntity, $stagingWebsite = null)
    {
        $stagingTablePrefix = $this->getTablePrefix();
        if (empty($stagingTablePrefix)) {
            return false;
        }

        $staging = Mage::getModel('enterprise_staging/staging');
        if (!is_null($stagingWebsite)) {
            $staging->loadByStagingWebsiteId($stagingWebsite->getId());
        }
        if (!Mage::getSingleton("core/session")->getData('staging_frontend_website_is_checked')) {
            $staging->checkFrontend($staging);
        }

        list($model, $entity) = split("[/]" , $modelEntity, 2);
        if (!$model){
            return false;
        }

        $globalTablePrefix = (string) Mage::getConfig()->getTablePrefix();

        $_tableName = $globalTablePrefix . $tableName;
        if ($this->isStagingUpTableName($model, $tableName)) {
            return $stagingTablePrefix . $_tableName;
        }

        return false;
    }

    /**
     * Check in staging config ig need to modify src table name
     *
     * @param string $model
     * @param string $tableName
     * @return bool
     */
    public function isStagingUpTableName($model, $tableName)
    {
        $itemSet = self::getConfig("staging_items");
        if ($itemSet) {
            foreach($itemSet->children() as $item) {
                $itemModel = (string) $item->model;
                if ($itemModel == $model) {
                    $isBackend = ((string)$item->is_backend === '1');
                    $useStorageMethod = (string) $item->use_storage_method;
                    if ($isBackend) {
                        $ignoreTables = (array) $item->ignore_tables;
                        //ignore for specified tables
                        if (!empty($ignoreTables)){
                            if (array_key_exists($tableName, $ignoreTables)) {
                                return false;
                            }
                        }
                        $tables = (array)  $item->entities;
                        //apply for specified tables
                        if (!empty($tables)){
                            if (!array_key_exists($tableName, $tables)) {
                                return false;
                            }
                        } else {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Retrieve core resources version
     *
     * @return  string
     */
    public function getCoreResourcesVersion()
    {
        $coreResource = Mage::getSingleton('core/resource');
        $connection  = $coreResource->getConnection('core_read');
        $select = $connection->select()->from($coreResource->getTableName('core/resource'), array('code' , 'version'));
        $result = $connection->fetchPairs($select);
        if (is_array($result) && count($result)>0) {
            return $result;
        } else {
            return array();
        }
    }
}
