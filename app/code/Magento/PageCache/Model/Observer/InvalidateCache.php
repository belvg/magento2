<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\PageCache\Model\Observer;

class InvalidateCache
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_typeList;

    /**
     * Application config object
     *
     * @var \Magento\PageCache\Model\Config
     */
    protected $_config;

    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $typeList
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\Framework\App\Cache\TypeListInterface $typeList
    ) {
        $this->_config = $config;
        $this->_typeList = $typeList;
    }

    /**
     * Invalidate full page cache
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_config->isEnabled()) {
            $this->_typeList->invalidate('full_page');
        }
        return $this;
    }
}
