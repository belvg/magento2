<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Magento_Config_Dom_Converter_ArrayConverterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Config_Dom_Converter_ArrayConverter
     */
    protected $_model;

    /**
     * Path to fixtures
     *
     * @var string
     */
    protected $_fixturePath;

    protected function setUp()
    {
        $this->_model = new Magento_Config_Dom_Converter_ArrayConverter();
        $this->_fixturePath = realpath(__DIR__ . '/../../')
            . DIRECTORY_SEPARATOR . '_files'
            . DIRECTORY_SEPARATOR . 'dom'
            . DIRECTORY_SEPARATOR . 'converter'
            . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $xml
     * @param string $array
     *
     * @dataProvider convertDataProvider
     */
    public function testConvert($xml, $array)
    {
        $xmlPath = $this->_fixturePath . $xml;
        $expected = require ($this->_fixturePath . $array);

        $dom = new DOMDocument();
        $dom->load($xmlPath);

        $actual = $this->_model->convert($dom->childNodes);
        $this->assertEquals($expected, $actual);
    }

    public function convertDataProvider()
    {
        return array(
            'no attributes'   => array('no_attributes.xml', 'no_attributes.php'),
            'with attributes' => array('with_attributes.xml', 'with_attributes.php'),
            'cdata' => array('cdata.xml', 'cdata.php'),
        );
    }
}