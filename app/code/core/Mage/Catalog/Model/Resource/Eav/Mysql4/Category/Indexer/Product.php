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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Indexer_Product extends Mage_Index_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('catalog/category_product_index', 'category_id');
    }

    /**
     * Process product save.
     * Method is responsible for index support when product was saved and assigned categories was changed.
     *
     * @param $event
     * @return unknown_type
     */
    public function productSave(Mage_Index_Model_Event $event)
    {
        //var_dump($event->getEntityPk());die();
    }

    public function categorySave(Mage_Index_Model_Event $event)
    {

    }

    /**
     * Rebuild all index data
     *
     * @return unknown_type
     */
    public function reindexAll()
    {
        $this->cloneIndexTable();
        $idxTable   = $this->getIdxTable();
        $idxAdapter = $this->_getIndexAdapter();

        $stores = $this->_getStoresInfo();
        foreach ($stores as $storeData) {
            $storeId    = $storeData['store_id'];
            $websiteId  = $storeData['website_id'];
            $rootPath   = $storeData['root_path'];
            $enabledTable   = $this->_prepareEnabledProductsVisibility($websiteId, $storeId);
            $anchorTable    = $this->_prepareAnchorCategories($storeId, $rootPath);

            $sql = "INSERT INTO {$idxTable}
                SELECT
                    cp.category_id, cp.product_id, cp.position, 1, {$storeId}, pv.visibility
                FROM
                    {$this->getTable('catalog/category_product')} AS cp
                    INNER JOIN {$enabledTable} AS pv ON pv.product_id=cp.product_id
                    LEFT JOIN {$anchorTable} AS ac ON ac.category_id=cp.category_id
                WHERE
                    ac.category_id IS NULL";
            $idxAdapter->query($sql);

            $anchorProductsTable = $this->_resources->getTableName('tmp_category_index_anchor_products');
            $idxAdapter->query('DROP TABLE IF EXISTS '.$anchorProductsTable);
            $sql = "CREATE TABLE `{$anchorProductsTable}` (
              `category_id` int(10) unsigned NOT NULL DEFAULT '0',
              `product_id` int(10) unsigned NOT NULL DEFAULT '0'
            ) ENGINE=MyISAM";

            $idxAdapter->query($sql);
            $sql = "SELECT
                    STRAIGHT_JOIN DISTINCT
                    ca.category_id, cp.product_id
                FROM {$anchorTable} AS ca
                  INNER JOIN {$this->getTable('catalog/category')} AS ce
                    ON ce.path LIKE ca.path
                  INNER JOIN {$this->getTable('catalog/category_product')} AS cp
                    ON cp.category_id = ce.entity_id
                  INNER JOIN {$enabledTable} as pv
                    ON pv.product_id = cp.product_id";
            $this->insertFromSelect($sql, $anchorProductsTable, array('category_id', 'product_id'));
            $sql = "INSERT INTO {$idxTable}
                SELECT
                    ap.category_id, ap.product_id, cp.position,
                    IF(cp.product_id, 1, 0), {$storeId}, pv.visibility
                FROM
                    {$anchorProductsTable} AS ap
                    LEFT JOIN {$this->getTable('catalog/category_product')} AS cp
                        ON cp.category_id=ap.category_id AND cp.product_id=ap.product_id
                    INNER JOIN {$enabledTable} as pv
                        ON pv.product_id = ap.product_id";
            $idxAdapter->query($sql);
        }
        $this->syncData();
        return $this;

    }

    /**
     * Get array with store|website|root_categry path information
     *
     * @return array
     */
    protected function _getStoresInfo()
    {
        $stores = $this->_getReadAdapter()->fetchAll("
            SELECT
                s.store_id, s.website_id, c.path AS root_path
            FROM
                {$this->getTable('core/store')} AS s,
                {$this->getTable('core/store_group')} AS sg,
                {$this->getTable('catalog/category')} AS c
            WHERE
                sg.group_id=s.group_id
                AND c.entity_id=sg.root_category_id
        ");
        return $stores;
    }

    /**
     * Create temporary table with enabled products visibility info
     *
     * @return string temporary table name
     */
    protected function _prepareEnabledProductsVisibility($websiteId, $storeId)
    {
        $statusAttribute        = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'status');
        $visibilityAttribute    = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'visibility');
        $statusAttributeId      = $statusAttribute->getId();
        $visibilityAttributeId  = $visibilityAttribute->getId();
        $statusTable            = $statusAttribute->getBackend()->getTable();
        $visibilityTable        = $visibilityAttribute->getBackend()->getTable();

        /**
         * Prepare temporary table
         */
        $tmpTable = $this->_resources->getTableName('tmp_category_index_enabled_products');
        $sql = 'DROP TABLE IF EXISTS ' . $tmpTable;
        $this->_getIndexAdapter()->query($sql);
        $sql = "CREATE TABLE {$tmpTable} (
           `product_id` int(10) unsigned NOT NULL DEFAULT '0',
           `visibility` int(11) unsigned NOT NULL DEFAULT '0',
           KEY `IDX_PRODUCT` (`product_id`)
         ) ENGINE=MyISAM";
        $this->_getIndexAdapter()->query($sql);

        $sql = "SELECT
                pe.entity_id AS product_id,
                IF(pvs.value_id>0, pvs.value, pvd.value) AS visibility
            FROM
                {$this->getTable('catalog/product')} AS pe
                INNER JOIN {$this->getTable('catalog/product_website')} AS pw
                    ON pw.product_id=pe.entity_id AND pw.website_id={$websiteId}
                LEFT JOIN {$visibilityTable} AS pvd
                    ON pvd.entity_id=pe.entity_id AND pvd.attribute_id={$visibilityAttributeId} AND pvd.store_id=0
                LEFT JOIN {$visibilityTable} AS pvs
                    ON pvs.entity_id=pe.entity_id AND pvs.attribute_id={$visibilityAttributeId} AND pvs.store_id={$storeId}
                LEFT JOIN {$statusTable} AS psd
                    ON psd.entity_id=pe.entity_id AND psd.attribute_id={$statusAttributeId} AND psd.store_id=0
                LEFT JOIN {$statusTable} AS pss
                    ON pss.entity_id=pe.entity_id AND pss.attribute_id={$statusAttributeId} AND pss.store_id={$storeId}
            WHERE
                IF(pss.value_id>0, pss.value, psd.value) = " . Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
        $this->insertFromSelect($sql, $tmpTable, array('product_id', 'visibility'));
        return $tmpTable;
    }

    /**
     * Create temporary table with list of anchor categories
     *
     * @param   int $storeId
     * @return  string temporary table name
     */
    protected function _prepareAnchorCategories($storeId)
    {
        $isAnchorAttribute  = Mage::getSingleton('eav/config')->getAttribute('catalog_category', 'is_anchor');
        $anchorAttributeId  = $isAnchorAttribute->getId();
        $anchorTable        = $isAnchorAttribute->getBackend()->getTable();

        $tmpTable = $this->_resources->getTableName('tmp_category_index_anchor_categories');
        $sql = 'DROP TABLE IF EXISTS ' . $tmpTable;
        $this->_getIndexAdapter()->query($sql);
        $sql = "CREATE TABLE {$tmpTable} (
            `category_id` int(10) unsigned NOT NULL DEFAULT '0',
            `path` varchar(257) CHARACTER SET utf8 NOT NULL DEFAULT '',
            KEY `IDX_CATEGORY` (`category_id`)
        ) ENGINE=MyISAM";
        $this->_getIndexAdapter()->query($sql);

        $sql = "SELECT
            ce.entity_id AS category_id,
            concat(ce.path, '/%') AS path
        FROM
            {$this->getTable('catalog/category')} as ce
            LEFT JOIN {$anchorTable} AS cad
                ON cad.entity_id=ce.entity_id AND cad.attribute_id={$anchorAttributeId} AND cad.store_id=0
            LEFT JOIN {$anchorTable} AS cas
                ON cas.entity_id=ce.entity_id AND cas.attribute_id={$anchorAttributeId} AND cas.store_id={$storeId}
        WHERE
            IF(cas.value_id>0, cas.value, cad.value) = 1";
        $this->insertFromSelect($sql, $tmpTable, array('category_id', 'path'));
        return $tmpTable;
    }

    protected function _removeTmpTables()
    {

    }


}