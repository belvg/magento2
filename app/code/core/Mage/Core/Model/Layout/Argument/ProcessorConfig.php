<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Layout config processor
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Layout_Argument_ProcessorConfig
{
    const LAYOUT_ARGUMENT_TYPE_OBJECT  = 'object';
    const LAYOUT_ARGUMENT_TYPE_OPTIONS = 'options';
    const LAYOUT_ARGUMENT_TYPE_URL     = 'url';

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_objectFactory;

    /**
     * Array of argument handler factories
     * @var array
     */
    protected $_handlerFactories = array();

    public function __construct(array $args = array())
    {
        if (!isset($args['objectFactory'])) {
            throw new InvalidArgumentException('Not all required parameters were passed');
        }
        $this->_objectFactory = $args['objectFactory'];
        if (false === ($this->_objectFactory instanceof Mage_Core_Model_Config)) {
            throw new InvalidArgumentException('Passed wrong instance of object factory');
        }
        $this->_handlerFactories = array(
            self::LAYOUT_ARGUMENT_TYPE_OBJECT  => 'Mage_Core_Model_Layout_Argument_Handler_ObjectFactory',
            self::LAYOUT_ARGUMENT_TYPE_OPTIONS => 'Mage_Core_Model_Layout_Argument_Handler_OptionsFactory',
            self::LAYOUT_ARGUMENT_TYPE_URL     => 'Mage_Core_Model_Layout_Argument_Handler_UrlFactory'
        );
    }

    /**
     * Get argument handler factory by given type
     * @param string $type
     * @return Mage_Core_Model_Layout_Argument_HandlerFactoryInterface
     * @throws InvalidArgumentException
     */
    public function getArgumentHandlerFactoryByType($type)
    {
        if (false == is_string($type)) {
            throw new InvalidArgumentException('Passed invalid argument handler type');
        }

        if (!isset($this->_handlerFactories[$type])) {
            throw new InvalidArgumentException('Argument handler ' . $type . ' is not exists');
        }

        /** @var $handlerFactory Mage_Core_Model_Layout_Argument_HandlerFactoryInterface */
        $handlerFactory = $this->_objectFactory->getModelInstance($this->_handlerFactories[$type]);

        return $handlerFactory;
    }
}