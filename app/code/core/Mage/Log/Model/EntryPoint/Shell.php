<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Log_Model_EntryPoint_Shell extends Mage_Core_Model_EntryPointAbstract
{
    /**
     * @param string $baseDir
     * @param array $params
     */
    public function __construct($baseDir, array $params = array())
    {
        $entryPoint = $params['entryPoint'];
        unset($params['entryPoint']);
        parent::__construct(new Mage_Core_Model_Config_Primary($baseDir, $params));
        $this->_objectManager->configure(array(
            'Mage_Log_Model_Shell' => array(
                'parameters' => array(
                    'entryPoint' => $entryPoint,
                )
            )
        ));
    }

    /**
     * Process request to application
     */
    public function processRequest()
    {
        /** @var $shell Mage_Log_Model_Shell */
        $shell = $this->_objectManager->create('Mage_Log_Model_Shell');
        $shell->run();
    }

}
