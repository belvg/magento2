<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product link model
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Alexander Stadnitski <alexander@varien.com>
 */
class Mage_Catalog_Model_Product_Image extends Mage_Core_Model_Abstract
{
    protected $_width;
    protected $_height;
    protected $_baseFile;
    protected $_newFile;
    protected $_processor;
    protected $_destinationSubdir;

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setWidth($width)
    {
        $this->_width = $width;
        return $this;
    }

    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setHeight($height)
    {
        $this->_height = $height;
        return $this;
    }

    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setSize($size)
    {
        $size = explode('x', strtolower($size));
        if( sizeof($size) < 2 ) {
            $this->setWidth(null)
                ->setHeight(null);
            return $this;
        }

        $this->setWidth( ($size[0] > 0) ? $size[0] : null )
            ->setHeight( ($size[1] > 0) ? $size[1] : null );

        return $this;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setBaseFile($file)
    {
        $baseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
        if( file_exists($baseDir . $file) ) {
            $this->_baseFile = $baseDir . $file;
        }

        $destDir = $baseDir;
        echo "<pre>DEBUG:\n";
        print_r($this);
        echo "</pre>";
        if( $this->getDestinationSubdir() ) {
            $destDir.= $baseDir . $this->getDestinationSubdir();
        }

        die($destDir);

        if( is_null($this->getWidth()) && is_null($this->getHeight()) ) {
            $this->_newFile = $baseDir . $file;
        } else {
            $this->_newFile = $baseDir . "/{$this->getWidth()}x{$this->getHeight()}" . $file;
        }
        return $this;
    }

    public function getBaseFile()
    {
        return $this->_baseFile;
    }

    public function getNewFile()
    {
        return $this->_newFile;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setImageProcessor($processor)
    {
        $this->_processor = $processor;
        return $this;
    }

    /**
     * @return Varien_Image
     */
    public function getImageProcessor()
    {
        if( !$this->_processor ) {
            $this->_processor = new Varien_Image($this->getBaseFile());
        }
        return $this->_processor;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function resize()
    {
        if( is_null($this->getWidth()) && is_null($this->getHeight()) ) {
            return $this;
        }
        $this->getImageProcessor()->resize($this->getWidth(), $this->getHeight());
        return $this;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function rotate($angle)
    {
        $angle = intval($angle);
        $this->getImageProcessor()->rotate($angle);
        return $this;
    }


    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setWatermark($filename)
    {
        if( !$filename ) {
            return $this;
        }

        $this->getImageProcessor()->watermark( Mage::getBaseDir('media') . '/' . $filename);
        return $this;
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function saveFile()
    {
        $this->getImageProcessor()->save($this->getNewFile());
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {

    }

    public function push()
    {
        $this->getImageProcessor()->display();
    }

    /**
     * @return Mage_Catalog_Model_Product_Image
     */
    public function setDestinationSubdir($dir)
    {
        die($dir);
        $this->_destinationSubdir = $dir;
        return $this;
    }

    /**
     * @return string
     */
    public function getDestinationSubdir()
    {
        return $this->_destinationSubdir;
    }
}