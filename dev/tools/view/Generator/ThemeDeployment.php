<?php
/**
 * {license_notice}
 *
 * @category   Tools
 * @package    view
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Transformation of files, which must be copied to new location and its contents processed
 */
class Generator_ThemeDeployment
{
    /**
     * Helper to process CSS content and fix urls
     *
     * @var Mage_Core_Helper_Css_Processing
     */
    private $_cssHelper;

    /**
     * Destination dir, where files will be copied to
     *
     * @var string
     */
    private $_destinationHomeDir;

    /**
     * List of extensions for files, which should be deployed.
     * For efficiency it is a map of ext => ext, so lookup by hash is possible.
     *
     * @var array
     */
    private $_permitted = array();

    /**
     * List of extensions for files, which must not be deployed
     * For efficiency it is a map of ext => ext, so lookup by hash is possible.
     *
     * @var array
     */
    private $_forbidden = array();

    /**
     * Whether to actually do anything inside the filesystem
     *
     * @var bool
     */
    private $_isDryRun;

    /**
     * Constructor
     *
     * @param Mage_Core_Helper_Css_Processing $cssHelper
     * @param string $destinationHomeDir
     * @param string $configPermitted
     * @param string|null $configForbidden
     * @param bool $isDryRun
     * @throws Magento_Exception
     */
    public function __construct(
        Mage_Core_Helper_Css_Processing $cssHelper,
        $destinationHomeDir,
        $configPermitted,
        $configForbidden = null,
        $isDryRun = false
    ) {
        $this->_cssHelper = $cssHelper;
        $this->_destinationHomeDir = $destinationHomeDir;
        $this->_isDryRun = $isDryRun;
        $this->_permitted = $this->_loadConfig($configPermitted);
        if ($configForbidden) {
            $this->_forbidden = $this->_loadConfig($configForbidden);
        }
        $conflicts = array_intersect($this->_permitted, $this->_forbidden);
        if ($conflicts) {
            $message = 'Conflicts: the following extensions are added both to permitted and forbidden lists: %s';
            throw new Magento_Exception(sprintf($message, implode(', ', $conflicts)));
        }
    }

    /**
     * Load config with file extensions
     *
     * @param string $path
     * @return array
     * @throws Magento_Exception
     */
    protected function _loadConfig($path)
    {
        if (!file_exists($path)) {
            throw new Magento_Exception("Config file does not exist: {$path}");
        }

        $contents = include($path);
        $contents = array_unique($contents);
        $contents = array_map('strtolower', $contents);
        $contents = $contents ? array_combine($contents, $contents) : array();
        return $contents;
    }

    /**
     * Copy all the files according to $copyRules
     *
     * @param array $copyRules
     */
    public function run($copyRules)
    {
        foreach ($copyRules as $copyRule) {
            $destinationContext = $copyRule['destinationContext'];
            $context = array(
                'source' => $copyRule['source'],
                'destinationContext' => $destinationContext,
            );

            $destDir = Mage_Core_Model_Design_Package::getPublishedViewFileRelPath(
                $destinationContext['area'],
                $destinationContext['themePath'],
                $destinationContext['locale'],
                '',
                $destinationContext['module']
            );
            $destDir = rtrim($destDir, '\\/');

            $this->_copyDirStructure(
                $copyRule['source'],
                $this->_destinationHomeDir . DIRECTORY_SEPARATOR . $destDir,
                $context
            );
        }
    }


    /**
     * Copy dir structure and files from $sourceDir to $destinationDir
     *
     * @param string $sourceDir
     * @param string $destinationDir
     * @param array $context
     * @throws Magento_Exception
     */
    protected function _copyDirStructure($sourceDir, $destinationDir, $context)
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($files as $fileSource) {
            $fileSource = (string) $fileSource;
            $extension = strtolower(pathinfo($fileSource, PATHINFO_EXTENSION));

            if (isset($this->_forbidden[$extension])) {
                continue;
            }

            if (!isset($this->_permitted[$extension])) {
                $message = sprintf(
                    'The file extension "%s" must be added either to the permitted or forbidden list. File: %s',
                    $extension,
                    $fileSource
                );
                throw new Magento_Exception($message);
            }

            $fileDestination = $destinationDir . substr($fileSource, strlen($sourceDir));
            $this->_deployFile($fileSource, $fileDestination, $context);
        }
    }

    /**
     * Deploy file to the destination path, also processing modular paths inside css-files.
     *
     * @param string $fileSource
     * @param string $fileDestination
     * @param array $context
     */
    protected function _deployFile($fileSource, $fileDestination, $context)
    {
        // Create directory
        $dir = dirname($fileDestination);
        if (!is_dir($dir) && !$this->_isDryRun) {
            mkdir($dir, 0777, true);
        }

        // Copy file
        $extension = pathinfo($fileSource, PATHINFO_EXTENSION);
        if (strtolower($extension) == 'css') { // For CSS files we need to process content and fix urls
            // Callback to resolve relative urls to the file names
            $destContext = $context['destinationContext'];
            $destHomeDir = $this->_destinationHomeDir;
            $destFileDir = dirname($fileDestination);
            $callback = function ($relativeUrl) use ($destContext, $destFileDir, $destHomeDir) {
                $parts = explode(Mage_Core_Model_Design_PackageInterface::SCOPE_SEPARATOR, $relativeUrl);
                if (count($parts) == 2) {
                    list($module, $file) = $parts;
                    if (!strlen($module) || !strlen($file)) {
                        throw new Magento_Exception("Wrong module url: {$relativeUrl}");
                    }
                    $relPath = Mage_Core_Model_Design_Package::getPublishedViewFileRelPath(
                        $destContext['area'], $destContext['themePath'], $destContext['locale'],
                        $file, $module
                    );

                    $result = $destHomeDir . '/' . $relPath;
                } else {
                    $result = $destFileDir . '/' . $relativeUrl;
                }
                return $result;
            };

            // Replace relative urls and write the modified content (if not dry run)
            $content = file_get_contents($fileSource);
            $content = $this->_cssHelper->replaceCssRelativeUrls($content, $fileDestination, $callback);
            if (!$this->_isDryRun) {
                file_put_contents($fileDestination, $content);
            }
        } else {
            if (!$this->_isDryRun) {
                copy($fileSource, $fileDestination);
            }
        }
    }
}
