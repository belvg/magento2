<?php

class Mage_Core_Controller_Zend_Request extends Zend_Controller_Request_Http 
{
    public function buildUrl()
    {
        $params = $this->getParams();
        $params['module'] = str_replace(' ','_',ucwords(str_replace('_',' ',$params['module'])));
        $params['module'] = (string)Mage::getConfig()->getModule($params['module'])->load->front->controller->frontName;
        
        if (!isset($params['protocol'])) {
            $params['protocol'] = 'http'.($this->getServer('HTTPS')?'s':'');
        }
        
        if (!isset($params['server'])) {
            $params['server'] = $this->getServer('HTTP_HOST');
        }
        
        $url = $params['protocol'].'://'.$params['server'];
        $url .= $this->getBaseUrl() !== '/' ? $this->getBaseUrl() : '';
        $url .= '/'.$params['module'].'/'.$params['controller'].'/'.$params['action'];
        
        foreach ($params as $key=>$value) {
            if ('module'!==$key && 'controller'!==$key && 'action'!==$key) {
                $url .= '/'.$key.'/'.$value;
            }
        }
        
        return $url;
    }
}