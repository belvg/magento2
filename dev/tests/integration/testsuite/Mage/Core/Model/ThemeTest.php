<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Core_Model_ThemeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test crud operations for theme model using valid data
     *
     * @magentoDbIsolation enabled
     */
    public function testCrud()
    {
        $themeModel = Mage::getModel('Mage_Core_Model_Theme');
        $themeModel->setData($this->_getThemeValidData());

        $crud = new Magento_Test_Entity($themeModel, array('theme_version' => '2.0.0.1'));
        $crud->testCrud();
    }

    /**
     * Load from configuration
     */
    public function testLoadFromConfiguration()
    {
        $themePath = implode(DS, array(__DIR__, '_files', 'design', 'frontend', 'default', 'default', 'theme.xml'));

        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = Mage::getModel('Mage_Core_Model_Theme');
        $themeModel->loadFromConfiguration($themePath);

        $this->assertEquals($this->_expectedThemeDataFromConfiguration(), $themeModel->getData());
    }

    /**
     * Expected theme data from configuration
     *
     * @return array
     */
    public function _expectedThemeDataFromConfiguration()
    {
        return array(
            'theme_code'           => 'default',
            'theme_title'          => 'Default',
            'theme_version'        => '2.0.0.0',
            'parent_theme'         => null,
            'is_featured'          => true,
            'magento_version_from' => '2.0.0.0-dev1',
            'magento_version_to'   => '*',
            'theme_path'           => 'default/default',
            'preview_image'        => '',
            'theme_directory'      => implode(
                DIRECTORY_SEPARATOR, array(__DIR__, '_files', 'design', 'frontend', 'default', 'default')
            )
        );
    }

    /**
     * Get theme valid data
     *
     * @return array
     */
    protected function _getThemeValidData()
    {
        return array(
            'theme_code'           => 'space',
            'theme_title'          => 'Space theme',
            'theme_version'        => '2.0.0.0',
            'parent_theme'         => null,
            'is_featured'          => false,
            'magento_version_from' => '2.0.0.0-dev1',
            'magento_version_to'   => '*',
            'theme_path'           => 'default/space',
            'preview_image'        => 'images/preview.png',
        );
    }

    /**
     * Test get preview image
     */
    public function testGetPreviewImageUrl()
    {
        $themeModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $themeModel->setPreviewImage('preview_image.jpg');
        $this->assertEquals('http://localhost/pub/media/theme/preview/preview_image.jpg',
                            $themeModel->getPreviewImageUrl());
    }

    /**
     * Test get preview image default
     */
    public function testGetPreviewImageDefaultUrl()
    {
        $defPreviewImageUrl = 'default_image_preview_url';
        $themeModel = $this->getMock('Mage_Core_Model_Theme', array('_getPreviewImageDefaultUrl'), array(), '', false);
        $themeModel->expects($this->once())
            ->method('_getPreviewImageDefaultUrl')
            ->will($this->returnValue($defPreviewImageUrl));

        $this->assertEquals($defPreviewImageUrl, $themeModel->getPreviewImageUrl());
    }

    /**
     * Test is virtual
     */
    public function testIsVirtual()
    {
        $themeCollection = new Mage_Core_Model_Theme_Collection();
        Mage::unregister('_singleton/Mage_Core_Model_Theme_Collection');
        Mage::register('_singleton/Mage_Core_Model_Theme_Collection', $themeCollection);

        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = Mage::getModel('Mage_Core_Model_Theme');
        $themeModel->setData($this->_getThemeValidData());

        $this->assertTrue($themeModel->isVirtual());

        $themeCollection->addItem($themeModel);
        $this->assertFalse($themeModel->isVirtual());
    }


    /**
     * Test id deletable
     *
     * @dataProvider isDeletableDataProvider
     */
    public function testIsDeletable($isVirtual)
    {
        $themeModel = $this->getMock('Mage_Core_Model_Theme', array('isVirtual'), array(), '', false);
        $themeModel->expects($this->once())
            ->method('isVirtual')
            ->will($this->returnValue($isVirtual));
        $this->assertEquals($isVirtual, $themeModel->isDeletable());
    }

    /**
     * @return array
     */
    public function isDeletableDataProvider()
    {
        return array(array(true), array(false));
    }

    public function testIsThemeCompatible()
    {
        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = Mage::getModel('Mage_Core_Model_Theme');

        $themeModel->setMagentoVersionFrom('2.0.0.0')->setMagentoVersionTo('*');
        $this->assertFalse($themeModel->isThemeCompatible());

        $themeModel->setMagentoVersionFrom('1.0.0.0')->setMagentoVersionTo('*');
        $this->assertTrue($themeModel->isThemeCompatible());
    }

    public function testCheckThemeCompatible()
    {
        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = Mage::getModel('Mage_Core_Model_Theme');

        $themeModel->setMagentoVersionFrom('2.0.0.0')->setMagentoVersionTo('*')->setThemeTitle('Title');
        $themeModel->checkThemeCompatible();
        $this->assertEquals('Title (incompatible version)', $themeModel->getThemeTitle());

        $themeModel->setMagentoVersionFrom('1.0.0.0')->setMagentoVersionTo('*')->setThemeTitle('Title');
        $themeModel->checkThemeCompatible();
        $this->assertEquals('Title', $themeModel->getThemeTitle());
    }

    public function testGetLabelsCollection()
    {
        /** @var $themeModel Mage_Core_Model_Theme */
        $themeModel = Mage::getModel('Mage_Core_Model_Theme');
        $labelCollection = $this->_getLabelCollection();

        $this->assertEquals($labelCollection, $themeModel->getLabelsCollection(false));

        array_unshift($labelCollection, array(
            'value' => '',
            'label' => '-- Please Select --'
        ));
        $this->assertEquals($labelCollection, $themeModel->getLabelsCollection());
    }

    protected function _getLabelCollection()
    {
        return array(
            array(
                'value' => '1',
                'label' => 'Magento Fluid Design  (incompatible version)'
            ),
            array(
                'value' => '2',
                'label' => 'Magento Demo'
            ),
            array(
                'value' => '3',
                'label' => 'Magento Modern'
            ),
            array(
                'value' => '4',
                'label' => 'Magento Iphone'
            ),
            array(
                'value' => '5',
                'label' => 'Magento Blank'
            ),
            array(
                'value' => '6',
                'label' => 'Magento Demo Blue'
            ),
            array(
                'value' => '7',
                'label' => 'Magento Fixed Design'
            ),
            array(
                'value' => '8',
                'label' => 'Magento Iphone (HTML5)'
            ),
        );
    }
}
