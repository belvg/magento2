<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Shopping cart item render block
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Checkout_Block_Cart_Item_Renderer_Configurable extends Mage_Checkout_Block_Cart_Item_Renderer
{
    const CONFIGURABLE_PRODUCT_IMAGE= 'checkout/cart/configurable_product_image';
    const USE_PARENT_IMAGE          = 'parent';

    /**
     * Get item configurable product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getConfigurableProduct()
    {
        if ($option = $this->getItem()->getOptionByCode('product_type')) {
            return $option->getProduct();
        }
        return $this->getProduct();
    }

    /**
     * Get item configurable child product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getChildProduct()
    {
        if ($option = $this->getItem()->getOptionByCode('simple_product')) {
            return $option->getProduct();
        }
        return $this->getProduct();
    }

    /**
     * Get product thumbnail image
     *
     * @return Mage_Catalog_Model_Product_Image
     */
    public function getProductThumbnail()
    {
        $product = $this->getChildProduct();
        if (!$product || !$product->getData('thumbnail')
            || ($product->getData('thumbnail') == 'no_selection')
            || (Mage::getStoreConfig(self::CONFIGURABLE_PRODUCT_IMAGE) == self::USE_PARENT_IMAGE)) {
            $product = $this->getProduct();
        }
        return $this->helper('Mage_Catalog_Helper_Image')->init($product, 'thumbnail');
    }

    /**
     * Get item product name
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->getProduct()->getName();
    }

    /**
     * Get selected for configurable product attributes
     *
     * @return array
     */
    public function getProductAttributes()
    {
        $attributes = $this->getProduct()->getTypeInstance()
            ->getSelectedAttributesInfo($this->getProduct());
        return $attributes;
    }

    /**
     * Get list of all otions for product
     *
     * @return array
     */
    public function getOptionList()
    {
        /* @var $helper Mage_Catalog_Helper_Product_Configuration */
        $helper = Mage::helper('Mage_Catalog_Helper_Product_Configuration');
        $options = $helper->getConfigurableOptions($this->getItem());
        return $options;
    }
}