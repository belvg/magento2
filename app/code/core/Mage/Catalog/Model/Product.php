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
 * Catalog product model
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 * @author     Ivan Chepurnyi <mitch@varien.com>
 */
class Mage_Catalog_Model_Product extends Mage_Catalog_Model_Abstract
{
    protected $_typeInstance;
    protected $_priceModel = null;
    protected $_urlModel = null;

    protected $_eventPrefix = 'catalog_product';
    protected $_eventObject = 'product';

    protected static $_url;
    protected static $_urlRewrite;

    protected $_cachedLinkedProductsByType = array();
    protected $_linkedProductsForSave = array();

    /**
     * Super product attribute collection
     *
     * @var Mage_Core_Model_Mysql4_Collection_Abstract
     */
    protected $_superAttributeCollection = null;

    /**
     * Super product links collection
     *
     * @var Mage_Eav_Model_Mysql4_Entity_Collection_Abstract
     */
    protected $_superLinkCollection = null;

    /**
     * Initialize resources
     */
    protected function _construct()
    {
        $this->_priceModel = Mage::getSingleton('catalog/product_price');
        $this->_urlModel = Mage::getSingleton('catalog/product_url');
        $this->_init('catalog/product');
    }

    /**
     * Retrieve type instance
     *
     * Type instance implement type depended logic
     *
     * @return  Mage_Catalog_Model_Product_Type_Abstract
     */
    public function getTypeInstance()
    {
        if (!$this->_typeInstance) {
            $this->_typeInstance = Mage::getSingleton('catalog/product_type')->factory($this);
        }
        return $this->_typeInstance;
    }

    /**
     * Retrive product id by sku
     *
     * @param   string $sku
     * @return  integer
     */
    public function getIdBySku($sku)
    {
        return $this->_getResource()->getIdBySku($sku);
    }

    /**
     * Retrieve product category id
     *
     * @return int
     */
    public function getCategoryId()
    {
        if ($category = Mage::registry('current_category')) {
            return $category->getId();
        }
        return false;
    }

    /**
     * Retrieve product category
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCategory()
    {
        $category = $this->getData('category');
        if (is_null($category) && $this->getCategoryId()) {
            $category = Mage::getModel('catalog/category')->load($this->getCategoryId());
            $this->setCategory($category);
        }
        return $category;
    }

    /**
     * Retrieve product websites identifiers
     *
     * @return array
     */
    public function getWebsiteIds()
    {
        if (!$this->hasWebsiteIds()) {
            $ids = $this->_getResource()->getWebsiteIds($this);
            $this->setData('website_ids', $ids);
        }
        return $this->getData('website_ids');
    }

    /**
     * Retrieve product attributes
     *
     * if $groupId is null - retrieve all product attributes
     *
     * @param   int $groupId
     * @return  array
     */
    public function getAttributes($groupId = null, $skipSuper=false)
    {
        $productAttributes = $this->getTypeInstance()->getAttributes();
        if ($groupId) {
            $attributes = array();
            foreach ($productAttributes as $attribute) {
                if ($attribute->getAttributeGroupId() == $groupId) {
                    $attributes[] = $attribute;
                }
            }
        }
        else {
            $attributes = $productAttributes;
        }

        return $attributes;
    }

    /**
     * Saving product type related data
     *
     * @return unknown
     */
    protected function _afterSave()
    {
        $this->getTypeInstance()->save();
        return parent::_afterSave();
    }

/*******************************************************************************
 ** Price API
 */
    /**
     * Get product pricing value
     *
     * @param   array $value
     * @return  double
     */
    public function getPricingValue($value)
    {
        return $this->_priceModel->getPricingValue($value, $this);
    }

    /**
     * Get product tier price by qty
     *
     * @param   double $qty
     * @return  double
     */
    public function getTierPrice($qty=null)
    {
        return $this->_priceModel->getTierPrice($qty, $this);
    }

    /**
     * Count how many tier prices we have for the product
     *
     * @return  int
     */
    public function getTierPriceCount()
    {
        return $this->_priceModel->getTierPriceCount($this);
    }

    /**
     * Get formated by currency tier price
     *
     * @param   double $qty
     * @return  array || double
     */
    public function getFormatedTierPrice($qty=null)
    {
        return $this->_priceModel->getFormatedTierPrice($qty, $this);
    }

    /**
     * Get formated by currency product price
     *
     * @return  array || double
     */
    public function getFormatedPrice()
    {
        return $this->_priceModel->getFormatedPrice($this);
    }

    /**
     * Get product final price
     *
     * @param double $qty
     * @return double
     */
    public function getFinalPrice($qty=null)
    {
        return $this->_priceModel->getFinalPrice($qty, $this);
    }

    /**
     * Get calculated product price
     *
     * @param array $options
     * @return double
     */
    public function getCalculatedPrice(array $options)
    {
        return $this->_priceModel->getCalculatedPrice($options, $this);
    }

/*******************************************************************************
 ** Linked products API
 */
    protected function _getLinksCollection()
    {

    }

    public function getRelatedLinks()
    {

    }

    /**
     * Retrieve array of related roducts
     *
     * @return array
     */
    public function getRelatedProducts()
    {
        if (!$this->hasRelatedProducts()) {
            $this->setRelatedProducts(array());
        }
        return $this->getData('related_products');
    }

    public function getRelatedProductIds()
    {

    }

    /**
     * Retrieve collection related product
     */
    public function getRelatedProductCollection()
    {

    }


    public function getRelatedProductsLoaded()
    {
        return $this->getLinkedProductsLoaded('relation');
    }


    /**
     * Retrive attributes for media gallery
     *
     * @return array
     */
    public function getMediaAttributes()
    {
        $mediaAttributes = array();

        foreach ($this->getAttributes() as $attribute) {
            /* @var $attribute Mage_Eav_Model_Entity_Attribute */
            if($attribute->getFrontend()->getInputType() == 'media_image') {
                $mediaAttributes[] = $attribute;
            }
        }

        return $mediaAttributes;
    }

    /**
     * Retrive media gallery images
     *
     * @return Varien_Data_Collection
     */
    public function getMediaGalleryImages()
    {
        if(!$this->hasData('media_gallery_images') && is_array($this->getMediaGallery('images'))) {
            $images = new Varien_Data_Collection();
            foreach ($this->getMediaGallery('images') as $image) {
                if ($image['disabled']) {
                    continue;
                }
                $image['url'] = $this->getMediaConfig()->getMediaUrl($image['file']);
                $image['id'] = $image['value_id'];
                $image['path'] = $this->getMediaConfig()->getMediaPath($image['file']);
                $images->addItem(new Varien_Object($image));
            }
            $this->setData('media_gallery_images', $images);
        }

        return $this->getData('media_gallery_images');
    }

    /**
     * Retrive product media config
     *
     * @return Mage_Catalog_Model_Product_Media_Config
     */
    public function getMediaConfig()
    {
        return Mage::getSingleton('catalog/product_media_config');
    }

















    public function copy()
    {
        $this->getResource()->copy($this);
        return $this;
    }

    public function validate()
    {
        $this->getResource()->validate($this);
        return $this;
    }


    public function getLinkedProducts($linkType)
    {
        if(!isset($this->_cachedLinkedProductsByType[$linkType])) {
            $this->_cachedLinkedProductsByType[$linkType] = Mage::getResourceModel('catalog/product_link_collection');
            $this->_cachedLinkedProductsByType[$linkType]
                    ->setLinkType($linkType)
                    ->setProductId($this->getId())
                    ->setStoreId($this->getStoreId())
                    ->addLinkTypeFilter()
                    ->addProductFilter()
                    ->addStoreFilter();

                $attibutes = $this->_cachedLinkedProductsByType[$linkType]->getLinkAttributeCollection();
                foreach ($attibutes as $attibute) {
                    $this->_cachedLinkedProductsByType[$linkType]->addLinkAttributeToSelect($attibute->getCode());
                }
        }

        return $this->_cachedLinkedProductsByType[$linkType];
    }

    public function getLinkedProductsLoaded($linkType)
    {
        if(!$this->getLinkedProducts($linkType)->getIsLoaded()) {
            $this->getLinkedProducts($linkType)->load();
        }

        return $this->getLinkedProducts($linkType);
    }

    public function setLinkedProducts($linkType, array $linkAttibutes)
    {
        $this->addLinkedProductsForSave($linkType, $linkAttibutes);

        return $this;
    }

    public function addLinkedProductsForSave($linkType, array $data)
    {
        $this->_linkedProductsForSave[$linkType] = $data;
        return $this;
    }

    public function getLinkedProductsForSave()
    {
        return $this->_linkedProductsForSave;
    }

    public function setUpSellProducts(array $linkAttibutes)
    {
        return $this->setLinkedProducts('up_sell', $linkAttibutes);
    }

    public function getUpSellProducts()
    {
        return $this->getLinkedProducts('up_sell');
    }

    public function getUpSellProductsLoaded()
    {
        return $this->getLinkedProductsLoaded('up_sell');
    }

    public function setCrossSellProducts(array $linkAttibutes)
    {
        return $this->setLinkedProducts('cross_sell', $linkAttibutes);
    }

    public function getCrossSellProducts()
    {
        return $this->getLinkedProducts('cross_sell');
    }

    public function getCrossSellProductsLoaded()
    {
        return $this->getLinkedProductsLoaded('cross_sell');
    }

    public function setSuperGroupProducts(array $linkAttibutes)
    {
        return $this->setLinkedProducts('super', $linkAttibutes);
    }

    public function getSuperGroupProducts()
    {
        return $this->getLinkedProducts('super');
    }

    public function getSuperGroupProductsLoaded()
    {
        if(!$this->getSuperGroupProducts()->getIsLoaded()) {
            $this->getSuperGroupProducts()->load();
        }
        return $this->getSuperGroupProducts();
    }

    public function getSuperAttributesIds()
    {
        if(!$this->getData('super_attributes_ids') && $this->getId() && $this->isSuperConfig()) {
            $superAttributesIds = array();
            $superAttributes = $this->getSuperAttributes(true);
            foreach ($superAttributes as $superAttribute) {
                $superAttributesIds[] = $superAttribute->getAttributeId();
            }
            $this->setData('super_attributes_ids', $superAttributesIds);
        }

        return $this->getData('super_attributes_ids');
    }

    /**
     * Checkin attribute availability for superproduct
     *
     * @param   Mage_Eav_Model_Entity_Attribute $attribute
     * @return  bool
     */
    public function canUseAttributeForSuperProduct(Mage_Eav_Model_Entity_Attribute $attribute)
    {
        return $attribute->getIsGlobal()
            && $attribute->getIsVisible()
            && $attribute->getUseInSuperProduct()
            && $attribute->getIsUserDefined()
            && ($attribute->getSourceModel() || $attribute->getBackendType()=='int' );
    }

    public function setSuperAttributesIds(array $attributesIds)
    {
        $resultAttributesIds = array();
        foreach ($this->getAttributes() as $attribute) {
            if(in_array($attribute->getAttributeId(), $attributesIds) && $this->canUseAttributeForSuperProduct($attribute)) {
                $resultAttributesIds[] = $attribute->getAttributeId();
            }
        }

        if(count($resultAttributesIds)>0) {
            $this->setData('super_attributes_ids', $resultAttributesIds);
        } else {
            $this->setData('super_attributes_ids', null);
        }

        return $this;
    }

    public function getSuperAttributes($asObject=false, $useLinkFilter=false)
    {
        return $this->getResource()->getSuperAttributes($this, $asObject, $useLinkFilter);
    }

    public function setSuperAttributes(array $superAttributes)
    {
        $this->setSuperAttributesForSave($superAttributes);
        return $this;
    }

    public function getSuperLinks()
    {
        return $this->getResource()->getSuperLinks($this);
    }

    public function getSuperLinkIdByOptions(array $options = null)
    {
        if(is_null($options)) {
            return false;
        }

        foreach ($this->getSuperLinks() as $linkId=>$linkAttributes) {
            $have_it = true;
            foreach ($linkAttributes as $attribute) {
                if(isset($options[$attribute['attribute_id']]) && $options[$attribute['attribute_id']]!=$attribute['value_index']) {
                    $have_it = false;
                }
            }
            if($have_it) {
                return $linkId;
            }
        }

        return false;
    }

    public function setSuperLinks(array $superLinks)
    {
        $this->setSuperLinksForSave($superLinks);
        return $this;
    }

    public function getSuperAttributesForSave()
    {
        if(!$this->getData('super_attributes_for_save') && strlen($this->getBaseStoreId())>0 && $this->getId()) {
            return $this->getSuperAttributes(false);
        }

        return $this->getData('super_attributes_for_save');
    }

    public function getSuperLinksForSave()
    {
        if(!$this->getData('super_links_for_save') && strlen($this->getBaseStoreId())>0 && $this->getId()) {
            return $this->getSuperLinks();
        }

        return $this->getData('super_links_for_save') ? $this->getData('super_links_for_save') : array();
    }

    public function isBundle()
    {
        return $this->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE;
    }

    public function isSuperGroup()
    {
        return $this->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED;
    }

    public function isSuperConfig()
    {
        return $this->isConfigurable();
    }

    public function isConfigurable()
    {
        return $this->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE;
    }

    public function isSuper()
    {
        return $this->isSuperConfig() || $this->isSuperGroup();
    }

    public function getSuperAttributeCollection()
    {
        if(!$this->isSuperConfig()) {
            return false;
        }

        if(is_null($this->_superAttributeCollection)) {
            $this->_superAttributeCollection = $this->getResource()->getSuperAttributeCollection($this);
        }

        return $this->_superAttributeCollection;
    }

    public function getSuperAttributeCollectionLoaded()
    {
        if(!$this->getSuperAttributeCollection()->getIsLoaded()) {
            $this->getSuperAttributeCollection()->load();
        }

        return $this->getSuperAttributeCollection();
    }

    public function getSuperLinkCollection()
    {
        if(!$this->isSuperConfig()) {
            return false;
        }

        if(is_null($this->_superLinkCollection)) {
            $this->_superLinkCollection = $this->getResource()->getSuperLinkCollection($this);
        }

        return $this->_superLinkCollection;
    }

    public function getSuperLinkCollectionLoaded()
    {
        if(!$this->getSuperLinkCollection()->getIsLoaded()) {
            $this->getSuperLinkCollection()->load();
        }

        return $this->getSuperLinkCollection();
    }

    /**
     * Retrieve product categories
     *
     * @return Varien_Data_Collection
     */
    public function getCategoryCollection()
    {
        $collection = $this->getResource()->getCategoryCollection($this);
        return $collection;
    }


    /**
     * Retrieve product stores collection
     *
     * @return unknown
     */
    public function getStoreCollection()
    {
        $collection = $this->getResource()->getStoreCollection($this);
        return $collection;
    }

    public function getVisibleInCatalogStatuses()
    {
        return Mage::getSingleton('catalog/product_status')->getVisibleStatusIds();
    }

    public function isVisibleInCatalog()
    {
        return in_array($this->getStatus(), $this->getVisibleInCatalogStatuses());
    }

    public function isSalable()
    {
        $salable = $this->getData('is_salable');
        if (!is_null($salable)) {
            return $salable;
        }
        return $this->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
    }

    public function isSaleable()
    {
        return $this->isSalable();
    }

    public function isInStock()
    {
        return $this->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
    }

    public function getAttributeText($attributeCode)
    {
        return $this->getResource()
            ->getAttribute($attributeCode)
                ->getSource()
                    ->getOptionText($this->getData($attributeCode));
    }

    public function getCustomDesignDate()
    {
        $result = array();
        $result['from'] = $this->getData('custom_design_from');
        $result['to'] = $this->getData('custom_design_to');

        return $result;
    }

    /**
     * Get product url
     *
     * @return string
     */
    public function getProductUrl()
    {
        return $this->_urlModel->getProductUrl($this);
    }

    public function formatUrlKey($str)
    {
        return $this->_urlModel->formatUrlKey($str);
    }

    public function getUrlPath($category=null)
    {
        return $this->_urlModel->getUrlPath($this, $category);
    }

    public function getImageUrl()
    {
        return $this->_urlModel->getImageUrl($this);
    }

    public function getCustomImageUrl($size, $extension=null, $watermark=null)
    {
        return $this->_urlModel->getCustomImageUrl($this, $size, $extension, $watermark);
    }

    public function getSmallImageUrl()
    {
        return $this->_urlModel->getSmallImageUrl($this);
    }

    public function getCustomSmallImageUrl($size, $extension=null, $watermark=null)
    {
        return $this->_urlModel->getCustomSmallImageUrl($this, $size, $extension, $watermark);
    }

    public function getThumbnailUrl()
    {
        return $this->_urlModel->getThumbnailUrl($this);
    }

    public function importFromTextArray(array $row)
    {
        $hlp = Mage::helper('catalog');

        // validate SKU
        if (empty($row['sku'])) {
            Mage::throwException($hlp->__('SKU is required'));
        }

        $catalogConfig = Mage::getSingleton('catalog/config');

        if (empty($row['entity_id'])) {
            $row['entity_id'] = $this->getIdBySku($row['sku']);
        }
        if (!empty($row['entity_id'])) {
            $this->unsetData();
            $this->load($row['entity_id']);
        } else {
            $this->setStoreId(0);

            // if attribute_set not set use default
            if (empty($row['attribute_set'])) {
                $row['attribute_set'] = !empty($row['attribute_set_id']) ? $row['attribute_set_id'] : 'Default';
            }
            // get attribute_set_id, if not throw error
            $attributeSetId = $catalogConfig->getAttributeSetId('catalog_product', $row['attribute_set']);
            if (!$attributeSetId) {
                Mage::throwException($hlp->__("Invalid attribute set specified"));
            }
            $this->setAttributeSetId($attributeSetId);

            if (empty($row['type'])) {
                $row['type'] = !empty($row['type_id']) ? $row['type_id'] : 'Simple Product';
            }
            // get product type_id, if not throw error
            $typeId = $catalogConfig->getProductTypeId($row['type']);
            if (!$typeId) {
                Mage::throwException($hlp->__("Invalid product type specified"));
            }
            $this->setTypeId($typeId);
        }

        $entity = $this->getResource();
        foreach ($row as $field=>$value) {
            $attribute = $entity->getAttribute($field);
            if (!$attribute) {
                continue;
            }

            if ($attribute->usesSource()) {
                $source = $attribute->getSource();
                $optionId = $catalogConfig->getSourceOptionId($source, $value);
                if (is_null($optionId)) {
                    Mage::throwException($hlp->__("Invalid attribute option specified for attribute %s (%s)", $field, $value));
                }
                $value = $optionId;
            }

            $this->setData($field, $value);
        }//foreach ($row as $field=>$value)

        $postedStores = array(0=>0);
        if (isset($row['store'])) {
            foreach (explode(',', $row['store']) as $store) {
                $storeId = Mage::app()->getStore($store)->getId();
                if (!$this->hasStoreId()) {
                    $this->setStoreId($storeId);
                }
                $postedStores[$storeId] = $this->getStoreId();
            }
        }
        $this->setPostedStores($postedStores);

        if (isset($row['categories'])) {
            $this->setPostedCategories($row['categories']);
        }

        return $this;
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        if (is_string($this->getCategoryIds())) {
            $this->setCategoryIds(explode(',', $this->getCategoryIds()));
        }
    }

    protected function _beforeSave()
    {
        parent::_afterLoad();
        if (is_array($this->getCategoryIds())) {
            $this->setCategoryIds(implode(',', $this->getCategoryIds()));
        }
    }
}