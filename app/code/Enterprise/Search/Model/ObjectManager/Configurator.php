<?php
/**
 * Object manager configurator
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Enterprise_Search_Model_ObjectManager_Configurator extends Mage_Core_Model_ObjectManager_ConfigAbstract
{
    /**
     * Configure di instance
     *
     * @param Magento_ObjectManager $objectManager
     */
    public function configure(Magento_ObjectManager $objectManager)
    {
        if (extension_loaded('solr')) {
            $adapter = 'Enterprise_Search_Model_Adapter_PhpExtension';
        } else {
            $adapter = 'Enterprise_Search_Model_Adapter_HttpStream';
        }
        $objectManager->configure(array(
            'preferences' => array(
                'Enterprise_Search_Model_Adapter_Interface' => $adapter
            )
        ));
    }
}
