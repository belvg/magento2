<?php
/**
 *  Application state flags
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Core_Model_App_State
{
    /**
     * Application mode
     *
     * @var string
     */
    private $_appMode;

    /**
     * Application modes
     */
    const MODE_DEVELOPER       = 'developer';
    const MODE_PRODUCTION      = 'production';
    const MODE_DEFAULT         = 'default';

    /**
     * @param string $mode
     * @throws Mage_Core_Exception
     */
    public function __construct($mode = self::MODE_DEFAULT)
    {
        switch ($mode) {
            case self::MODE_DEVELOPER:
            case self::MODE_PRODUCTION:
            case self::MODE_DEFAULT:
                $this->_appMode = $mode;
                break;
            default:
                throw new Mage_Core_Exception("Unknown application mode: {$mode}");
        }
    }

    /**
     * Check if application is installed
     *
     * @return bool
     */
    public function isInstalled()
    {
        return Mage::isInstalled();
    }

    /**
     * Return current app mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->_appMode;
    }

    /**
     * Set update mode flag
     *
     * @param bool $value
     */
    public function setUpdateMode($value)
    {
        Mage::setUpdateMode($value);
    }

    /**
     * Get update mode flag
     *
     * @return bool
     */
    public function getUpdateMode()
    {
        return Mage::getUpdateMode();
    }

    /**
     * Set is downloader flag
     *
     * @param bool $flag
     */
    public function setIsDownloader($flag = true)
    {
        Mage::setIsDownloader($flag);
    }

    /**
     * Set is serializable flag
     *
     * @param bool $value
     */
    public function setIsSerializable($value = true)
    {
        Mage::setIsSerializable($value);
    }

    /**
     * Get is serializable flag
     *
     * @return bool
     */
    public function getIsSerializable()
    {
        return Mage::getIsSerializable();
    }
}