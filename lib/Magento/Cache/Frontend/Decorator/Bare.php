<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Cache frontend decorator that attaches no additional responsibility to a decorated instance.
 * To be used as an ancestor for concrete decorators to conveniently override only methods of interest.
 */
class Magento_Cache_Frontend_Decorator_Bare implements Magento_Cache_FrontendInterface
{
    /**
     * Cache frontend instance to delegate actual cache operations to
     *
     * @var Magento_Cache_FrontendInterface
     */
    private $_frontend;

    /**
     * @param Magento_Cache_FrontendInterface $frontend
     */
    public function __construct(Magento_Cache_FrontendInterface $frontend)
    {
        $this->_frontend = $frontend;
    }

    /**
     * Retrieve cache frontend instance being decorated
     *
     * @return Magento_Cache_FrontendInterface
     */
    protected function _getFrontend()
    {
        return $this->_frontend;
    }

    /**
     * {@inheritdoc}
     */
    public function test($id)
    {
        return $this->_frontend->test($id);
    }

    /**
     * {@inheritdoc}
     */
    public function load($id)
    {
        return $this->_frontend->load($id);
    }

    /**
     * Enforce marking with a tag
     *
     * {@inheritdoc}
     */
    public function save($data, $id, array $tags = array(), $lifeTime = null)
    {
        return $this->_frontend->save($data, $id, $tags, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($id)
    {
        return $this->_frontend->remove($id);
    }

    /**
     * {@inheritdoc}
     */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, array $tags = array())
    {
        return $this->_frontend->clean($mode, $tags);
    }

    /**
     * {@inheritdoc}
     */
    public function getBackend()
    {
        return $this->_frontend->getBackend();
    }

    /**
     * {@inheritdoc}
     */
    public function getLowLevelFrontend()
    {
        return $this->_frontend->getLowLevelFrontend();
    }
}
