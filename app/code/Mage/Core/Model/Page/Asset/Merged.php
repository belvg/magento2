<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Iterator that aggregates one or more assets and provides a single public file with equivalent behavior
 */
class Mage_Core_Model_Page_Asset_Merged implements Iterator
{
    /**
     * Sub path for merged files relative to public view cache directory
     */
    const PUBLIC_MERGE_DIR  = '_merged';

    /**
     * @var Magento_ObjectManager
     */
    private $_objectManager;

    /**
     * @var Mage_Core_Model_Logger
     */
    private $_logger;

    /**
     * @var Mage_Core_Model_Page_Asset_MergeStrategy_MergeStrategyInterface
     */
    private $_mergeStrategy;

    /**
     * @var Mage_Core_Model_Page_Asset_MergeableInterface[]
     */
    private $_assets;

    /**
     * @var string
     */
    private $_contentType;

    /**
     * Whether initialization has been performed or not
     *
     * @var bool
     */
    private $_isInitialized = false;

    /**
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Model_Logger $logger,
     * @param Mage_Core_Model_Dir $dirs,
     * @param Mage_Core_Model_Page_Asset_MergeStrategy_MergeStrategyInterface $mergeStrategy
     * @param array $assets
     * @throws InvalidArgumentException
     */
    public function __construct(
        Magento_ObjectManager $objectManager,
        Mage_Core_Model_Logger $logger,
        Mage_Core_Model_Dir $dirs,
        Mage_Core_Model_Page_Asset_MergeStrategy_MergeStrategyInterface $mergeStrategy,
        array $assets
    ) {
        $this->_objectManager = $objectManager;
        $this->_logger = $logger;
        $this->_dirs = $dirs;
        $this->_mergeStrategy = $mergeStrategy;

        if (!$assets) {
            throw new InvalidArgumentException('At least one asset has to be passed for merging.');
        }
        /** @var $asset Mage_Core_Model_Page_Asset_MergeableInterface */
        foreach ($assets as $asset) {
            if (!($asset instanceof Mage_Core_Model_Page_Asset_MergeableInterface)) {
                throw new InvalidArgumentException(
                    'Asset has to implement Mage_Core_Model_Page_Asset_MergeableInterface.'
                );
            }
            if (!$this->_contentType) {
                $this->_contentType = $asset->getContentType();
            } else if ($asset->getContentType() != $this->_contentType) {
                throw new InvalidArgumentException(
                    "Content type '{$asset->getContentType()}' cannot be merged with '{$this->_contentType}'."
                );
            }
        }
        $this->_assets = $assets;
    }

    /**
     * Attempt to merge assets, falling back to original non-merged ones, if merging fails
     */
    protected function _initialize()
    {
        if (!$this->_isInitialized) {
            $this->_isInitialized = true;
            try {
                $this->_assets = array($this->_getMergedAsset($this->_assets));
            } catch (Exception $e) {
                $this->_logger->logException($e);
            }
        }
    }

    /**
     * Retrieve asset instance representing a merged file
     *
     * @param Mage_Core_Model_Page_Asset_MergeableInterface[] $assets
     * @return Mage_Core_Model_Page_Asset_AssetInterface
     */
    protected function _getMergedAsset(array $assets)
    {
        $sourceFiles = $this->_getPublicFilesToMerge($assets);
        $destinationFile = $this->_getMergedFilePath($sourceFiles);

        $this->_mergeStrategy->mergeFiles($sourceFiles, $destinationFile);
        return $this->_objectManager->create('Mage_Core_Model_Page_Asset_PublicFile', array(
            'file' => $destinationFile,
            'contentType' => $this->_contentType,
        ));
    }

    /**
     * Go through all the files to merge, ensure that they are public (publish if needed), and compose
     * array of public paths to merge
     *
     * @param Mage_Core_Model_Page_Asset_MergeableInterface[] $assets
     * @return array
     */
    protected function _getPublicFilesToMerge(array $assets)
    {
        $result = array();
        foreach ($assets as $asset) {
            $publicFile = $asset->getSourceFile();
            $result[$publicFile] = $publicFile;
        }
        return $result;
    }

    /**
     * Return file name for the resulting merged file
     *
     * @param array $publicFiles
     * @return string
     */
    protected function _getMergedFilePath(array $publicFiles)
    {
        $jsDir = Magento_Filesystem::fixSeparator($this->_dirs->getDir(Mage_Core_Model_Dir::PUB_LIB));
        $publicDir = Magento_Filesystem::fixSeparator($this->_dirs->getDir(Mage_Core_Model_Dir::STATIC_VIEW));
        $prefixRemovals = array($jsDir, $publicDir);

        $relFileNames = array();
        foreach ($publicFiles as $file) {
            $file = Magento_Filesystem::fixSeparator($file);
            $relFileNames[] = str_replace($prefixRemovals, '', $file);
        }

        $mergedDir = $this->_dirs->getDir(Mage_Core_Model_Dir::PUB_VIEW_CACHE) . '/'
            . self::PUBLIC_MERGE_DIR;
        return $mergedDir . '/' . md5(implode('|', $relFileNames)) . '.' . $this->_contentType;
    }

    /**
     * {@inheritdoc}
     *
     * @return Mage_Core_Model_Page_Asset_AssetInterface
     */
    public function current()
    {
        $this->_initialize();
        return current($this->_assets);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        $this->_initialize();
        return key($this->_assets);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->_initialize();
        next($this->_assets);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->_initialize();
        reset($this->_assets);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        $this->_initialize();
        return (bool)current($this->_assets);
    }
}
