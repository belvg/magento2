<?php
/**
 * {license_notice}
 *
 * @category     Mage
 * @package      Mage_Backup
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Class to work with full filesystem and database backups
 *
 * @category    Mage
 * @package     Mage_Backup
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backup_Snapshot extends Mage_Backup_Filesystem
{
    /**
     * Database backup manager
     *
     * @var Mage_Backup_Db
     */
    protected $_dbBackupManager;

    /**
     * Implementation Rollback functionality for Snapshot
     *
     * @throws Mage_Exception
     * @return bool
     */
    public function rollback()
    {
        $result = parent::rollback();

        $this->_lastOperationSucceed = false;

        try {
            $this->_getDbBackupManager()->rollback();
        } catch (Exception $e) {
            $this->_removeDbBackup();
            throw $e;
        }

        $this->_removeDbBackup();
        $this->_lastOperationSucceed = true;

        return $result;
    }

    /**
     * Implementation Create Backup functionality for Snapshot
     *
     * @throws Mage_Exception
     * @return bool
     */
    public function create()
    {
        $this->_getDbBackupManager()->create();

        try {
            $result = parent::create();
        } catch (Exception $e) {
            $this->_removeDbBackup();
            throw $e;
        }

        $this->_lastOperationSucceed = false;
        $this->_removeDbBackup();
        $this->_lastOperationSucceed = true;

        return $result;
    }

    /**
     * Overlap getType
     *
     * @return string
     * @see Mage_Backup_Interface::getType()
     */
    public function getType()
    {
        return 'snapshot';
    }

    /**
     * Create Db Instance
     *
     * @return Mage_Backup_Interface
     */
    protected function _createDbBackupInstance()
    {
        return Mage_Backup::getBackupInstance(Mage_Backup_Helper_Data::TYPE_DB)
            ->setBackupExtension(Mage::helper('Mage_Backup_Helper_Data')->getExtensionByType(Mage_Backup_Helper_Data::TYPE_DB))
            ->setTime($this->getTime())
            ->setBackupsDir(Mage::getBaseDir("var"))
            ->setResourceModel($this->getResourceModel());
    }

    /**
     * Get database backup manager
     *
     * @return Mage_Backup_Db
     */
    protected function _getDbBackupManager()
    {
        if (is_null($this->_dbBackupManager)) {
            $this->_dbBackupManager = $this->_createDbBackupInstance();
        }

        return $this->_dbBackupManager;
    }

    /**
     * Remove Db backup after added it to the snapshot
     *
     * @return Mage_Backup_Snapshot
     */
    protected function _removeDbBackup(){
        @unlink($this->_getDbBackupManager()->getBackupPath());
        return $this;
    }
}