<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category    Enterprise
 * @package     Enterprise_Checkout
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Enterprise Checkout Helper
 *
 * @category    Enterprise
 * @package     Enterprise_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Items for requiring attention grid (doesn't include sku-failed items)
     *
     * @var null|array
     */
    protected $_items = null;

    /**
     * Items for requiring attention grid (including sku-failed items)
     *
     * @var null|array
     */
    protected $_itemsAll = null;

    /**
     * Config path to Enable Order By SKU tab in the Customer account dashboard and Allowed groups
     */
    const XML_PATH_SKU_ENABLED = 'sales/product_sku/my_account_enable';
    const XML_PATH_SKU_ALLOWED_GROUPS = 'sales/product_sku/allowed_groups';

    /**
     * Status of item, that was added by SKU
     */
    const ADD_ITEM_STATUS_SUCCESS = 'success';
    const ADD_ITEM_STATUS_FAILED_SKU = 'failed_sku';
    const ADD_ITEM_STATUS_FAILED_OUT_OF_STOCK = 'failed_out_of_stock';
    const ADD_ITEM_STATUS_FAILED_QTY_ALLOWED = 'failed_qty_allowed';
    const ADD_ITEM_STATUS_FAILED_CONFIGURE = 'failed_configure';
    const ADD_ITEM_STATUS_FAILED_PERMISSIONS = 'failed_permissions';
    const ADD_ITEM_STATUS_FAILED_UNKNOWN = 'failed_unknown';

    /**
     * Layout handle for sku failed items
     */
    const SKU_FAILED_PRODUCTS_HANDLE = 'sku_failed_products_handle';

    /**
     * Customer Groups that allow Order by SKU
     *
     * @var array|null
     */
    protected $_allowedGroups = null;

    /**
     * Contains session object to which data is saved
     *
     * @var Mage_Core_Model_Session_Abstract
     */
    protected $_session;

    /**
     * Return session for affected items
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    public function getSession()
    {
        if (!$this->_session) {
            $sessionClassPath = Mage::app()->getStore()->isAdmin() ? 'adminhtml/session' : 'customer/session';
            $this->_session =  Mage::getSingleton($sessionClassPath);
        }

        return $this->_session;
    }

    /**
     * Sets session instance to use for saving data
     *
     * @param Mage_Core_Model_Session_Abstract $session
     */
    public function setSession(Mage_Core_Model_Session_Abstract $session)
    {
        $this->_session = $session;
    }

    /**
     * Retrieve message by specified error code
     *
     * @param string $code
     * @return string
     */
    public function getMessage($code)
    {
        switch ($code) {
            case self::ADD_ITEM_STATUS_FAILED_SKU:
                $message = $this->__('SKU not found in catalog');
                break;
            case self::ADD_ITEM_STATUS_FAILED_OUT_OF_STOCK:
                $message = $this->__('Out of stock');
                break;
            case self::ADD_ITEM_STATUS_FAILED_QTY_ALLOWED:
                $message = $this->__('Requested quantity is not available');
                break;
            case self::ADD_ITEM_STATUS_FAILED_CONFIGURE:
                $message = $this->__("Please specify the product's options");
                break;
            case self::ADD_ITEM_STATUS_FAILED_PERMISSIONS:
                $message = $this->__("The product cannot be added to cart.");
                break;
            default:
                $message = '';
        }
        return $message;
    }

    /**
     * Check whether module enabled
     *
     * @return bool
     */
    public function isSkuEnabled()
    {
        $storeData = Mage::getStoreConfig(self::XML_PATH_SKU_ENABLED);
        return Enterprise_Checkout_Model_Cart_Sku_Source_Settings::NO_VALUE != $storeData;
    }

    /**
     * Check whether Order by SKU functionality applicable to the current customer
     *
     * @return bool
     */
    public function isSkuApplied()
    {
        $result = false;
        $data = Mage::getStoreConfig(self::XML_PATH_SKU_ENABLED);
        switch ($data) {
            case Enterprise_Checkout_Model_Cart_Sku_Source_Settings::YES_VALUE:
                $result = true;
                break;
            case Enterprise_Checkout_Model_Cart_Sku_Source_Settings::YES_SPECIFIED_GROUPS_VALUE:
                /** @var $customerSession Mage_Customer_Model_Session */
                $customerSession = Mage::getSingleton('customer/session');
                if ($customerSession) {
                    $customer = $customerSession->getCustomer();
                    if ($customer) {
                        $customerGroup = $customer->getGroupId();
                        $result = in_array($customerGroup, $this->getSkuCustomerGroups());
                    }
                }
                break;
        }
        return $result;
    }

    /**
     * Retrieve Customer Groups that allow Order by SKU from config
     *
     * @return array
     */
    public function getSkuCustomerGroups()
    {
        if ($this->_allowedGroups === null) {
            $this->_allowedGroups = explode(',', trim(Mage::getStoreConfig(self::XML_PATH_SKU_ALLOWED_GROUPS)));
        }
        return $this->_allowedGroups;
    }

    /**
     * Get add by SKU failed items
     *
     * @param bool $all whether sku-failed items should be retrieved
     * @return array
     */
    public function getFailedItems($all = true)
    {
        if ($all && is_null($this->_itemsAll) || !$all && is_null($this->_items)) {
            $failedItems = Mage::getModel('enterprise_checkout/cart')->getFailedItems();
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addUrlRewrite();
            $itemsToLoad = array();

            $quoteItemsCollection = is_null($this->_items) ? array() : $this->_items;

            foreach ($failedItems as $item) {
                if (is_null($this->_items)
                    && $item['code'] != Enterprise_Checkout_Helper_Data::ADD_ITEM_STATUS_FAILED_SKU
                ) {
                    $itemsToLoad[$item['item']['id']] = array(
                        'qty' => $item['item']['qty'],
                        'code' => $item['code'],
                        'error' => isset($item['error']) ? $item['error'] : '',
                    );
                } elseif ($all && $item['code'] == Enterprise_Checkout_Helper_Data::ADD_ITEM_STATUS_FAILED_SKU) {
                    $item['item']['code'] = $item['code'];
                    $item['item']['product_type'] = 'undefined';
                    $quoteItemsCollection[] = new Varien_Object($item['item']);
                }
            }
            $ids = array_keys($itemsToLoad);
            if ($ids) {
                $collection->addIdFilter($ids);

                $quote = Mage::getSingleton('checkout/session')->getQuote();
                $emptyQuoteItem = Mage::getModel('sales/quote_item');

                /** @var $product Mage_Catalog_Model_Product */
                foreach ($collection->getItems() as $product) {
                    $product->setQty($itemsToLoad[$product->getId()]['qty']);
                    $product->setCode($itemsToLoad[$product->getId()]['code']);
                    $product->setError($itemsToLoad[$product->getId()]['error']);
                    if (!$product->getOptionsByCode()) {
                        $product->setOptionsByCode(array());
                    }
                    // Create a new quote item and import data to it
                    $quoteItem = clone $emptyQuoteItem;
                    $quoteItem->addData($product->getData())
                        ->setQuote($quote)
                        ->setProduct($product)
                        ->setOptions($product->getOptions())
                        ->setRedirectUrl($product->getUrlModel()->getUrl($product));

                    $product->setCustomOptions($product->getOptionsByCode());
                    if (Mage::helper('catalog')->canApplyMsrp($product)) {
                        $quoteItem->setCanApplyMsrp(true);
                        $product->setRealPriceHtml(
                            Mage::app()->getStore()->formatPrice(Mage::app()->getStore()->convertPrice(
                                Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true)
                            ))
                        );
                        $product->setAddToCartUrl(Mage::helper('checkout/cart')->getAddUrl($product));
                    } else {
                        $quoteItem->setCanApplyMsrp(false);
                    }

                    $quoteItemsCollection[] = $quoteItem;
                }
            }

            if ($all) {
                $this->_itemsAll = $quoteItemsCollection;
            } else {
                $this->_items = $quoteItemsCollection;
            }
        }
        return $all ? $this->_itemsAll : $this->_items;
    }
}
