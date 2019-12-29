<?php

namespace ThiagoDias\NewsletterModal\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data 
{
    protected $_scopeConfig;

    const MODULE_PATH = 'thiagodias_newslettermodal/general/';

    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
       
    }

    public function getIsModuleEnable() {
        
        return $this->_scopeConfig->getValue(self::MODULE_PATH.'enable', ScopeInterface::SCOPE_STORE);
    
    }

    public function getModalTitle() {
        
        return $this->_scopeConfig->getValue(self::MODULE_PATH.'title', ScopeInterface::SCOPE_STORE);
    
    }

    public function getModalDescription() {
        
        return $this->_scopeConfig->getValue(self::MODULE_PATH.'description', ScopeInterface::SCOPE_STORE);
    
    }
}