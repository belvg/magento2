<?php
/**
 * Adminhtml customer grid block
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Adminhtml_Block_Customer_Online_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('onlineGrid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() 
    {
    	parent::_prepareCollection();
    	foreach ($this->getCollection()->getItems() as $item) {
        	$item->addIpData($item)
                 ->addCustomerData($item)
        	     ->addQuoteData($item);
        }
        return $this;
    }
    
    protected function _initCollection()
    {
        $filterOnlineOnly = $this->getRequest()->getParam('filterOnline', false);
        $filterCustomersOnly = $this->getRequest()->getParam('filterCustomers', false);
        $filterGuestsOnly = $this->getRequest()->getParam('filterGuests', false);

        $collection = Mage::getResourceSingleton('log/visitor_collection');

        if( $filterCustomersOnly ) {
            $collection->showCustomersOnly();
        }

        if( $filterGuestsOnly ) {
            $collection->showGuestsOnly();
        }

        if( $filterOnlineOnly === false ) {
            $collection->useOnlineFilter();
        }

        $this->setCollection($collection);
    }

    protected function _beforeToHtml()
    {
        $this->addColumn('id', array('header'=>__('id'), 'width'=>'40px', 'align'=>'center', 'index'=>'customer_id'));
        $this->addColumn('firstname', array('header'=>__('firstname'),  'index'=>'customer_firstname'));
        $this->addColumn('lastname', array('header'=>__('lastname'), 'index'=>'customer_lastname'));
        $this->addColumn('email', array('header'=>__('email'), 'align'=>'center', 'index'=>'customer_email'));
        $this->addColumn('ip_address', array('header'=>__('ip_address'), 'align'=>'center', 'index'=>'remote_addr', 
        									'renderer'=>'adminhtml/customer_online_grid_renderer_ip'));
        $this->addColumn('session_start_time', array('header'=>__('session_start_time'), 'align'=>'center', 'index'=>'first_visit_at'));
        $this->addColumn('last_activity', array('header'=>__('last_activity'), 'align'=>'center', 'index'=>'last_visit_at'));
        $this->addColumn('last_url', array('header'=>__('last_url'), 'align'=>'center', 'index'=>'url'));
        $this->addColumn('cart_items', array('header'=>__('cart_items'), 'align'=>'center', 'index'=>'quote_data'));
        $this->_initCollection();
        return parent::_beforeToHtml();
    }
}