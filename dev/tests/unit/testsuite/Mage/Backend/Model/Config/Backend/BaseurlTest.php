<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Backend_Model_Config_Backend_BaseurlTest extends PHPUnit_Framework_TestCase
{
    public function testSaveMergedJsCssMustBeCleaned()
    {
        $eventDispatcher = $this->getMock('Mage_Core_Model_Event_Manager', array(), array(), '', false);
        $cacheManager = $this->getMock('Mage_Core_Model_CacheInterface');
        $context = new Mage_Core_Model_Context($eventDispatcher, $cacheManager);

        $resource = $this->getMock('Mage_Core_Model_Resource_Config_Data', array(), array(), '', false);
        $resource->expects($this->any())
            ->method('addCommitCallback')
            ->will($this->returnValue($resource));
        $resourceCollection = $this->getMock('Varien_Data_Collection_Db');
        $mergeService = $this->getMock('Mage_Core_Model_Page_Asset_MergeService', array(), array(), '', false);

        $model = $this->getMock(
            'Mage_Backend_Model_Config_Backend_Baseurl',
            array('getOldValue'),
            array($context, $mergeService, $resource, $resourceCollection)
        );
        $mergeService->expects($this->once())
            ->method('cleanMergedJsCss');

        $model->setValue('http://example.com/')
            ->setPath(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL);
        $model->save();
    }
}
