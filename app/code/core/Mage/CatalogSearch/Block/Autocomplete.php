<?php

class Mage_CatalogSearch_Block_Autocomplete extends Mage_Core_Block_Abstract
{
    public function toHtml()
    {
		if (!$this->_beforeToHtml()) {
			return '';
		}

        $query = $this->getRequest()->getParam('query', '');
        $searchCollection = Mage::getResourceModel('catalogsearch/search_collection')
        	->addFieldToFilter('num_results', array('gt'=>0))
            ->addFieldToFilter('search_query', array('like'=>$query.'%'))
            ->setOrder('popularity', 'desc')
            ->setPageSize(20);
            
        $searchCollection
            ->getSelect()->orWhere('synonims regexp ?', '(^|,)\s*'.$query.'.*(,|$)');
            
        $searchCollection->loadData();
        $items = $searchCollection->getItems();

        if (sizeof($items)==0) {
        	return '';
        }
        if (sizeof($items)>0) {
        	$found = false;
        	foreach ($items as $i=>$item) {
        		if ($item->getSearchQuery()==$query) {
        			$found = true;
        			unset($items[$i]);
        			array_unshift($items, $item);
        		}
        	}
        	/*
        	if (!$found) {
	        	$default = Mage::getModel('catalogsearch/search')->setSearchQuery($query);
	        	array_unshift($items, $default);
        	}
        	*/
        }
        $i=0;
        $html = '<ul><li style="display:none"></li>';
        foreach ($items as $item) {
            $html .= '<li title="'.$item->getSearchQuery().'" class="'.((++$i)%2?'odd':'even').'"><div style="float:right">'.$item->getNumResults().'</div>'.$item->getSearchQuery().'</li>';
        }
        $html .= '</ul>';
        
        return $html;
    }
}