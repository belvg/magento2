<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @category    Mage
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_XmlConnect_Helper_Image extends Mage_Core_Helper_Abstract
{
    const XMLCONNECT_GLUE = '_';

    /**
     * Image limits for content
     *
     * @var array|null
     */
    protected $_content = null;

    /**
     * Image limits for interface
     *
     * @var array|null
     */
    protected $_interface = null;

    /**
     * Array of interface image paths in xmlConfig
     *
     * @var array
     */
    protected $_interfacePath = array();

    /**
     * Image limits array
     *
     * @var array
     */
    protected $_imageLimits = array();

    /**
     * Images paths in the config
     *
     * @var array|null
     */
    protected $_confPaths = null;
    /**
     * Process uploaded file
     * setup filenames to the configuration
     *
     * @param string $field
     * @param mixed &$target
     * @retun string
     */
    public function handleUpload($field, &$target)
    {
        $uploadedFilename = '';
        $uploadDir = $this->getOriginalSizeUploadDir();

        $this->_forcedConvertPng($field);

        try {
            $uploader = new Varien_File_Uploader($field);
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->save($uploadDir);
            $uploadedFilename = $uploader->getUploadedFileName();
            $uploadedFilename = $this->_getResizedFilename($field, $uploadedFilename, true);
        } catch (Exception $e) {
            /**
             * Hard coded exception catch
             */
            if ($e->getMessage() == 'Disallowed file type.') {
                $filename = $_FILES[$field]['name'];
                Mage::throwException(Mage::helper('xmlconnect')->__('Error while uploading file "%s". Disallowed file type. Only "jpg", "jpeg", "gif", "png" are allowed.', $filename));
            } else {
                Mage::logException($e);
            }
        }
        return $uploadedFilename;
    }

    /**
     * Return current screen_size parameter
     *
     * @return string
     */
    protected function _getScreenSize()
    {
        return Mage::registry('current_app')->getScreenSize(); 
    }

    /**
     * Return correct system filename for current screenSize
     *
     * @param string $fieldPath
     * @param string $fileName
     * @param string $default
     * @return string
     */
    protected function _getResizedFilename($fieldPath, $fileName, $default = false)
    {
        $fileName = basename($fileName);
        if ($default) {
            $dir = $this->getDefaultSizeUploadDir();
        } else {
            $dir = $this->getCustomSizeUploadDir($this->_getScreenSize());
        }
        $customSizeFileName =  $dir . DS . $fileName;
        $originalSizeFileName = $this->getOriginalSizeUploadDir(). DS . $fileName;
        $error = false;
        /**
         * Compatibility with old versions of XmlConnect
         */
        if (!file_exists($originalSizeFileName)) {
            $oldFileName = $this->getOldUploadDir() . DS . $fileName;
            if (file_exists($oldFileName)) {
                if (!(copy($oldFileName, $originalSizeFileName) &&
                    (is_readable($customSizeFileName) || chmod($customSizeFileName, 0644)))) {
                    Mage::throwException(Mage::helper('xmlconnect')->__('Error while processing file "%s".', $fileName));
                }
            } else {
                Mage::throwException(Mage::helper('xmlconnect')->__('No such file "%s".', $fileName));
            }
        }

        if ((!$error) && copy($originalSizeFileName, $customSizeFileName) &&
            (is_readable($customSizeFileName) || chmod($customSizeFileName, 0644))) {
            $this->_handleResize($fieldPath, $customSizeFileName);
        } else {
            $fileName = '';
            if (isset($_FILES[$fieldPath]) && is_array($_FILES[$fieldPath]) && isset($_FILES[$fieldPath]['name'])) {
                $fileName = $_FILES[$fieldPath]['name'];
            }
            Mage::throwException(Mage::helper('xmlconnect')->__('Error while uploading file "%s".', $fileName));
        }
        return $customSizeFileName;
    }

    /**
     * Resize uploaded file
     *
     * @param string $fieldPath
     * @param string $file
     * @return void
     */
    protected function _handleResize($fieldPath, $file)
    {
        $nameParts = explode('/', $fieldPath);
        array_shift($nameParts);
        $conf = $this->getInterfaceImageLimits();
        while (count($nameParts)) {
            $next = array_shift($nameParts);
            if (isset($conf[$next])) {
                $conf = $conf[$next];
            } else {
                /**
                 * No config data - nothing to resize
                 */
                return;
            }
        }

        $image = new Varien_Image($file);
        $width = $image->getOriginalWidth();
        $height = $image->getOriginalHeight();

        if (isset($conf['widthMax']) && ($conf['widthMax'] < $width)) {
            $width = $conf['widthMax'];
        } elseif (isset($conf['width'])) {
            $width = $conf['width'];
        }

        if (isset($conf['heightMax']) && ($conf['heightMax'] < $height)) {
            $height = $conf['heightMax'];
        } elseif (isset($conf['height'])) {
            $height = $conf['height'];
        }

        if (($width != $image->getOriginalWidth()) ||
            ($height != $image->getOriginalHeight()) ) {
            $image->keepTransparency(true);
            $image->keepFrame(true);
            $image->keepAspectRatio(true);
            $image->backgroundColor(array(255, 255, 255));
//            $image->keepAspectRatio(false);
            $image->resize($width, $height);
            $image->save(null, basename($file));
        }
    }

    /**
     * Convert uploaded file to PNG
     *
     * @param string $field
     */
    protected function _forcedConvertPng($field)
    {
        $file =& $_FILES[$field];

        $file['name'] = preg_replace('/\.(gif|jpeg|jpg)$/i', '.png', $file['name']);

        list($x, $x, $fileType) = getimagesize($file['tmp_name']);
        if ($fileType != IMAGETYPE_PNG ) {
            switch( $fileType ) {
                case IMAGETYPE_GIF:
                    $img = imagecreatefromgif($file['tmp_name']);
                    break;
                case IMAGETYPE_JPEG:
                    $img = imagecreatefromjpeg($file['tmp_name']);
                    break;
                default:
                    return;
            }
            imagealphablending($img, false);
            imagesavealpha($img, true);
            imagepng($img, $file['tmp_name']);
            imagedestroy($img);
        }
    }

    /**
     * Retrieve xmlconnect images skin url
     *
     * @param string $name
     * @return string
     */
    public function getSkinImagesUrl($name = null)
    {
        return Mage::getDesign()->getSkinUrl('images/xmlconnect/' . $name);
    }

    /**
     * Return CustomSizeDirPrefix
     *
     * @return string
     */
    public function getCustomSizeDirPrefix()
    {
        return $this->_getScreenSize() . DS . 'custom';
    }

    /**
     * Return FileDefaultSizeSuffixAsUrl
     *
     * @param string $fileName
     * @return string
     */
    public function getFileDefaultSizeSuffixAsUrl($fileName)
    {
        return 'custom'.'/'.$this->_getScreenSize().'/'.basename($fileName);
    }

    /**
     * Return getFileCustomDirSuffixAsUrl
     *
     * @param string $confPath
     * @param string $fileName
     * @return string
     */
    public function getFileCustomDirSuffixAsUrl($confPath, $fileName)
    {
        return 'custom'.'/'.$this->_getScreenSize().'/'.basename($this->_getResizedFilename($confPath, $fileName));
    }

    /**
     * Return correct size for given $imageName and device screen_size
     * 
     * @param string $imageName
     * @return int
     */
    public function getImageSizeForContent($imageName) 
    {
        $size = 0;
        if (!isset($this->_content)) {
            $app = Mage::registry('current_app');
            if (!$app) {
                return 0;
            } else {
                $imageLimits = $this->getImageLimits($this->_getScreenSize());
                if (($imageLimits['content']) && is_array($imageLimits['content'])) {
                    $this->_content = $imageLimits['content'];
                } else {
                    $this->_content = array();
                }
            }
        }
        $size = isset($this->_content[$imageName]) ? (int) $this->_content[$imageName] : 0; 
        return $size;
    }

    /**
     * Return setting for interface images (image size limits)
     * 
     * @return array
     */
    public function getInterfaceImageLimits()
    {
        if (!isset($this->_interface)) {
            $imageLimits = $this->getImageLimits($this->_getScreenSize());
            $this->_interface = $imageLimits['interface'];
        }
        return $this->_interface;
    }

    /**
     * Return correct size for given $imageName and device screen_size
     *
     * @param string $imagePath
     * @return int
     */
    public function getImageSizeForInterface($imagePath)
    {
        $size = 0;
        if (!isset($this->_interfacePath[$imagePath])) {
            $app = Mage::registry('current_app');
            if (!$app) {
                return 0;
            } else {
                $imageLimits = $this->getImageLimits($this->_getScreenSize());
                $size = $this->findPath($imageLimits, $imagePath);
                $this->_interfacePath[$imagePath] = $size;
            }
        }
        $size = isset($this->_interfacePath[$imagePath]) ? (int) $this->_interfacePath[$imagePath] : 0;
        return $size;
    }


    /**
     * Ensure correct $screenSize value
     *
     * @param string $screenSize
     * @return string
     */
    public function filterScreenSize($screenSize)
    {
        
        $screenSize = preg_replace('/[^0-9A-z_]/', '', $screenSize);
        if (isset($this->_imageLimits[$screenSize])) {
            return $screenSize;
        }
        $screenSizeExplodeArray = explode(self::XMLCONNECT_GLUE, $screenSize);
        $version = '';
        switch (count($screenSizeExplodeArray)) {
            case 2:
                $version = $screenSizeExplodeArray[1];
            case 1:
                $resolution = $screenSizeExplodeArray[0];
                break;
            default:
                $resolution = Mage_XmlConnect_Model_Application::APP_SCREEN_SIZE_DEFAULT;
                break;
        }

        $sourcePath = empty($version) ? Mage_XmlConnect_Model_Application::APP_SCREEN_SOURCE_DEFAULT : $version;
        $xmlPath = 'screen_size/'.self::XMLCONNECT_GLUE.$resolution.'/'.$sourcePath.'/source';


        $source = Mage::getStoreConfig($xmlPath);
        if (!empty($source)) {
            $screenSize = $resolution . (empty($version) ? '' : self::XMLCONNECT_GLUE.$version);
        } else {
             $screenSize = Mage_XmlConnect_Model_Application::APP_SCREEN_SIZE_DEFAULT;
        }
        return $screenSize;
    }

    /**
     * Return correct size array for given device screen_size(320x480/640x960_a)
     * 
     * @param string $screenSize
     * @return array
     */
    public function getImageLimits($screenSize = Mage_XmlConnect_Model_Application::APP_SCREEN_SIZE_DEFAULT)
    {
        $defaultScreenSize = Mage_XmlConnect_Model_Application::APP_SCREEN_SIZE_DEFAULT;
        $defaultScreenSource = Mage_XmlConnect_Model_Application::APP_SCREEN_SOURCE_DEFAULT;

        $screenSize = preg_replace('/[^0-9A-z_]/', '', $screenSize);
        if (isset($this->_imageLimits[$screenSize])) {
            return $this->_imageLimits[$screenSize];
        }
        $screenSizeExplodeArray = explode(self::XMLCONNECT_GLUE, $screenSize);
        $version = '';
        switch (count($screenSizeExplodeArray)) {
            case 2:
                $version = $screenSizeExplodeArray[1];
            case 1:
                $resolution = $screenSizeExplodeArray[0];
                break;
            default:
                $resolution = $defaultScreenSize;
                break;
        }

        $sourcePath = empty($version) ? $defaultScreenSource : $version;
        $xmlPath = 'screen_size/'.self::XMLCONNECT_GLUE.$resolution.'/'.$sourcePath;

        $root = Mage::getStoreConfig($xmlPath);
        $updates = array();
        if (!empty($root)) {
            $screenSize = $resolution . (empty($version) ? '' : self::XMLCONNECT_GLUE.$version);
            $source = !empty($root['source']) ? $root['source'] : $defaultScreenSource;
            $updates = isset($root['updates']) && is_array($root['updates']) ? $root['updates'] : array();
        } else {
            $screenSize = $defaultScreenSize;
            $source = $defaultScreenSource;
        }
        $imageLimits = Mage::getStoreConfig('screen_size/'.$source);
        if (!is_array($imageLimits)) {
            $imageLimits = Mage::getStoreConfig('screen_size/default');
        }

        foreach ($updates as $update) {
            $path = $update['path'];
            $function = $update['function'];
            switch ($function) {
                case 'zoom':
                    $data = $update['data'];
                    $target =& $this->findPath($imageLimits, $path);
                    if (is_array($target)) {
                        array_walk_recursive($target, array($this, '_zoom'), $data);
                    } else {
                        $this->_zoom($target, null, $data);
                    }
                    break;
                case 'update':
                    $data = $update['data'];
                    $target =& $this->findPath($imageLimits, $path);
                    $target = $data;
                    break;
                case 'insert':
                    $data = $update['data'];
                    $target =& $this->findPath($imageLimits, $path);
                    if (($target !== null) && (is_array($target)) && (is_array($data))) {
                        foreach ($data as $key => $val) {
                            $target[$key] = $val;
                        }
                    }
                    break;
                case 'delete':
                    $data = $update['data'];
                    $target =& $this->findPath($imageLimits, $path);
                    if (isset($target[$data])) {
                        unset($target[$data]);
                    }
                    break;
                default:
                    break;
            }

        }
        if (!is_array($imageLimits)) {
            $imageLimits = array();
        }

        $this->_imageLimits[$screenSize] = $imageLimits; 
        return $imageLimits;
    }

    /**
     * Return reference to the $path in $array
     * 
     * @param array $array
     * @param string $path
     * @return &mixed    //(reference)
     */
    public function &findPath(&$array, $path)
    {
        $target =& $array;
        if ($path !== '/') {
            $pathArray = explode('/', $path);
            foreach ($pathArray as $node) {
                if (is_array($target) && isset($target[$node])) {
                    $target =& $target[$node];
                } else {
                    $targetNull = null;
                    return $targetNull;
                }
            }
        }
        return $target;
    }

    /**
     * Multiply given $item by $value if non array
     *
     * @param mixed $item (argument to change)
     * @param mixed $key (not used)
     * @param string $value (contains float)
     * @return void
     */
    protected function _zoom(&$item, $key, $value)
    {
        if (is_string($item)) {
            $item = (int) round($item*$value);
        }
    }

    /**
     * Ensure $dir exists (if not then create one)
     *
     * @param string $dir
     * @throw Mage_Core_Exception
     */
    protected function _verifyDirExist($dir)
    {
        $io = new Varien_Io_File();
        $io->checkAndCreateFolder($dir);
    }

    /**
     * Return customSizeUploadDir path
     *
     * @param string $screenSize
     * @return string
     */
    public function getCustomSizeUploadDir($screenSize)
    {
        $screenSize = $this->filterScreenSize($screenSize);
        $customDirRoot = Mage::getBaseDir('media') . DS . 'xmlconnect' . DS . 'custom';
        $this->_verifyDirExist($customDirRoot);
        $customDir = $customDirRoot . DS .$screenSize;
        $this->_verifyDirExist($customDir);
        return $customDir;
    }

    /**
     * Return originalSizeUploadDir path
     * 
     * @return string
     */
    public function getOriginalSizeUploadDir()
    {
        $dir = Mage::getBaseDir('media') . DS . 'xmlconnect' . DS . 'original';
        $this->_verifyDirExist($dir);
        return $dir;
    }

    /**
     * Return oldUpload dir path  (media/xmlconnect)
     * 
     * @return string
     */
    public function getOldUploadDir()
    {
        $dir = Mage::getBaseDir('media') . DS . 'xmlconnect';
        $this->_verifyDirExist($dir);
        return $dir;
    }

    /**
     * Return default size upload dir path
     * 
     * @return string
     */
    public function getDefaultSizeUploadDir()
    {
        return $this->getCustomSizeUploadDir(Mage_XmlConnect_Model_Application::APP_SCREEN_SIZE_DEFAULT);
    }

    /**
     * Return array for interface images paths in the config
     *
     * @return array
     */
    public function getInterfaceImagesPathsConf()
    {
        if (!isset($this->_confPaths)) {
            $paths = $this->getInterfaceImagesPaths();
            $this->_confPaths = array();
            $len = strlen('conf/native/');
            foreach ($paths as $path => $defaultFileName) {
                $this->_confPaths[$path] = substr($path, $len);
            }
        }
        return $this->_confPaths;
    }

    /**
     *  Return 1) default interface image path for specified $imagePath
     *         2) array of image paths
     *
     * @param string $imagePath
     * @return array|string
     */
    public function getInterfaceImagesPaths($imagePath = null)
    {
        $paths = array (
            'conf/native/navigationBar/icon' => 'smallIcon_1_6.png',
            'conf/native/body/bannerImageLandscape' => 'banner_1_2_l.png',
            'conf/native/body/bannerImagePortret' => 'banner_1_2_p.png',
            'conf/native/body/bannerImage' => 'banner_1_2.png',
            'conf/native/body/backgroundImageLandscape' => 'accordion_open_l.png',
            'conf/native/body/backgroundImagePortret' => 'accordion_open_p.png',
            'conf/native/body/backgroundImage' => 'accordion_open.png',
        );
        if ($imagePath == null) {
            return $paths;
        } else if (isset($paths[$imagePath])) {
            return $paths[$imagePath];
        } else {
            return null;
        }
    }
}
