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
 * @package    Mage_Directory
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Directory_Model_Mysql4_Region
{
    protected $_regionTable;
    protected $_regionNameTable;

    /**
     * DB read connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_read;

    /**
     * DB write connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_write;

    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->_regionTable     = $resource->getTableName('directory/country_region');
        $this->_regionNameTable = $resource->getTableName('directory/country_region_name');
        $this->_read    = $resource->getConnection('directory_read');
        $this->_write   = $resource->getConnection('directory_write');
    }

    public function getIdFieldName()
    {
        return 'region_id';
    }

    public function load(Mage_Directory_Model_Region $region, $regionId)
    {
        $lang = Mage::app()->getStore()->getLanguageCode();

        $select = $this->_read->select()
            ->from($this->_regionTable)
            ->where($this->_regionTable.".region_id=?", $regionId)
            ->join($this->_regionNameTable, $this->_regionNameTable.'.region_id='.$this->_regionTable.'.region_id
                AND '.$this->_regionNameTable.".language_code='$lang'");

        $region->setData($this->_read->fetchRow($select));
        return $this;
    }

    public function loadByCode(Mage_Directory_Model_Region $region, $regionCode, $countryId)
    {
        $lang = Mage::app()->getStore()->getLanguageCode();

        $select = $this->_read->select()
            ->from($this->_regionTable)
            ->where($this->_regionTable.".country_id=?", $countryId)
            ->where($this->_regionTable.".code=?", $regionCode)
            ->join($this->_regionNameTable, $this->_regionNameTable.'.region_id='.$this->_regionTable.'.region_id
                AND '.$this->_regionNameTable.".language_code='$lang'");

        $region->setData($this->_read->fetchRow($select));
        return $this;
    }
}
