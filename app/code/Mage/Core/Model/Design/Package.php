<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */


class Mage_Core_Model_Design_Package implements Mage_Core_Model_Design_PackageInterface
{
    /**#@+
     * Common node path to theme design configuration
     */
    const XML_PATH_THEME    = 'design/theme/full_name';
    const XML_PATH_THEME_ID = 'design/theme/theme_id';
    /**#@-*/

    /**
     * Path to configuration node that indicates how to materialize view files: with or without "duplication"
     */
    const XML_PATH_ALLOW_DUPLICATION = 'global/design/theme/allow_view_files_duplication';

    /**
     * XPath for configuration setting of signing static files
     */
    const XML_PATH_STATIC_FILE_SIGNATURE = 'dev/static/sign';

    /**#@+
     * Public directories prefix group
     */
    const PUBLIC_MODULE_DIR = '_module';
    const PUBLIC_VIEW_DIR   = '_view';
    const PUBLIC_THEME_DIR  = '_theme';
    /**#@-*/

    /**
     * Regular expressions matches cache
     *
     * @var array
     */
    private static $_regexMatchCache      = array();

    /**
     * Custom theme type cache
     *
     * @var array
     */
    private static $_customThemeTypeCache = array();

    /**
     * Package area
     *
     * @var string
     */
    protected $_area;

    /**
     * Package theme
     *
     * @var Mage_Core_Model_Theme
     */
    protected $_theme;

    /**
     * Directory of the css file
     * Using only to transmit additional parameter in callback functions
     *
     * @var string
     */
    protected $_callbackFileDir;

    /**
     * Array of theme model used for fallback mechanism
     *
     * @var array
     */
    protected $_themes = array();

    /**
     * Store list manager
     *
     * @var Mage_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * View file system model
     *
     * @var Mage_Core_Model_View
     */
    protected $_viewFileSystem;

    /**
     * View file URL model
     *
     * @var Mage_Core_Model_View_Url
     */
    protected $_viewUrl;

    /**
     * View config model
     *
     * @var Mage_Core_Model_View_Config
     */
    protected $_viewConfig;

    /**
     * View service model
     *
     * @var Mage_Core_Model_View_Service
     */
    protected $_viewService;

    /**
     * Design
     *
     * @param Mage_Core_Model_StoreManagerInterface $storeManager
     * @param Mage_Core_Model_View_FileSystem $viewFileSystem
     * @param Mage_Core_Model_View_Url $viewUrl
     * @param Mage_Core_Model_View_Config $viewConfig
     * @param Mage_Core_Model_View_Service $viewService
     */
    public function __construct(
        Mage_Core_Model_StoreManagerInterface $storeManager,
        Mage_Core_Model_View_FileSystem $viewFileSystem,
        Mage_Core_Model_View_Url $viewUrl,
        Mage_Core_Model_View_Config $viewConfig,
        Mage_Core_Model_View_Service $viewService
    ) {
        $this->_storeManager = $storeManager;
        $this->_viewFileSystem = $viewFileSystem;
        $this->_viewUrl = $viewUrl;
        $this->_viewConfig = $viewConfig;
        $this->_viewService = $viewService;
    }

    /**
     * Set package area
     *
     * @param string $area
     * @return Mage_Core_Model_Design_Package
     */
    public function setArea($area)
    {
        $this->_area = $area;
        $this->_theme = null;
        return $this;
    }

    /**
     * Retrieve package area
     *
     * @return string
     */
    public function getArea()
    {
        if (is_null($this->_area)) {
            $this->_area = self::DEFAULT_AREA;
        }
        return $this->_area;
    }

    /**
     * Load design theme
     *
     * @param int|string $themeId
     * @param string|null $area
     * @return Mage_Core_Model_Theme
     */
    public function loadDesignTheme($themeId, $area = self::DEFAULT_AREA)
    {
        $key = sprintf('%s/%s', $area, $themeId);
        if (isset($this->_themes[$key])) {
            return $this->_themes[$key];
        }

        if (is_numeric($themeId)) {
            $themeModel = clone $this->getDesignTheme();
            $themeModel->load($themeId);
        } else {
            /** @var $collection Mage_Core_Model_Resource_Theme_Collection */
            $collection = $this->getDesignTheme()->getCollection();
            $themeModel = $collection->getThemeByFullPath($area . '/' . $themeId);
        }
        $this->_themes[$key] = $themeModel;

        return $themeModel;
    }

    /**
     * Set theme path
     *
     * @param Mage_Core_Model_Theme|int|string $theme
     * @param string $area
     * @return Mage_Core_Model_Design_Package
     */
    public function setDesignTheme($theme, $area = null)
    {
        if ($area) {
            $this->setArea($area);
        }

        if ($theme instanceof Mage_Core_Model_Theme) {
            $this->_theme = $theme;
        } else {
            $this->_theme = $this->loadDesignTheme($theme, $this->getArea());
        }

        return $this;
    }

    /**
     * Get default theme which declared in configuration
     *
     * Write default theme to core_config_data
     *
     * @param string $area
     * @param array $params
     * @return string|int
     */
    public function getConfigurationDesignTheme($area = null, array $params = array())
    {
        if (!$area) {
            $area = $this->getArea();
        }

        $theme = null;
        $store = isset($params['store']) ? $params['store'] : null;

        if ($this->_isThemePerStoveView($area)) {
            $theme = $this->_storeManager->isSingleStoreMode()
                ? (string)Mage::getConfig()->getNode('default/' . self::XML_PATH_THEME_ID)
                : (string)Mage::getStoreConfig(self::XML_PATH_THEME_ID, $store);
        }

        return $theme ?: (string)Mage::getConfig()->getNode($area . '/' . self::XML_PATH_THEME);
    }

    /**
     * Whether themes in specified area are supposed to be configured per store view
     *
     * @param string $area
     * @return bool
     */
    private function _isThemePerStoveView($area)
    {
        return $area == self::DEFAULT_AREA;
    }

    /**
     * Set default design theme
     *
     * @return Mage_Core_Model_Design_Package
     */
    public function setDefaultDesignTheme()
    {
        $this->setDesignTheme($this->getConfigurationDesignTheme());
        return $this;
    }

    /**
     * Design theme model getter
     *
     * @return Mage_Core_Model_Theme
     */
    public function getDesignTheme()
    {
        if ($this->_theme === null) {
            $this->_theme = Mage::getModel('Mage_Core_Model_Theme');
        }
        return $this->_theme;
    }

    /**
     * Return package name based on design exception rules
     *
     * @param array $rules - design exception rules
     * @param string $regexpsConfigPath
     * @return bool|string
     */
    public static function getPackageByUserAgent(array $rules, $regexpsConfigPath = 'path_mock')
    {
        foreach ($rules as $rule) {
            if (!empty(self::$_regexMatchCache[$rule['regexp']][$_SERVER['HTTP_USER_AGENT']])) {
                self::$_customThemeTypeCache[$regexpsConfigPath] = $rule['value'];
                return $rule['value'];
            }

            $regexp = '/' . trim($rule['regexp'], '/') . '/';

            if (@preg_match($regexp, $_SERVER['HTTP_USER_AGENT'])) {
                self::$_regexMatchCache[$rule['regexp']][$_SERVER['HTTP_USER_AGENT']] = true;
                self::$_customThemeTypeCache[$regexpsConfigPath] = $rule['value'];
                return $rule['value'];
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDesignParams()
    {
        $params = array(
            'area'       => $this->getArea(),
            'themeModel' => $this->getDesignTheme(),
            'locale'     => Mage::app()->getLocale()->getLocaleCode()
        );

        return $params;
    }


    // methods delegated to FileSystem model

    public function __call($name, $args = array())
    {
        if (in_array($name, array(/*'getViewFileUrl',*/ 'getViewFilePublicPath', 'getPublicFileUrl'))) {
            $object = $this->_viewUrl;
        } elseif (in_array($name, array('getFilename', 'getLocaleFileName', 'getViewFile'))) {
            $object = $this->_viewFileSystem;
        } elseif (in_array($name, array('getViewConfig'))) {
            $object = $this->_viewConfig;
        } elseif (in_array($name, array('getPublicDir'))) {
            $object = $this->_viewService;
        } else {
            throw new Exception(sprintf('Method "%s" not found in MCMDP', $name));
        }
        return call_user_func_array(array($object, $name), $args);
    }

}
