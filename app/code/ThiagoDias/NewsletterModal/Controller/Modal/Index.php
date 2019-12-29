<?php

namespace ThiagoDias\NewsletterModal\Controller\Modal;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

use ThiagoDias\NewsletterModal\Helper\Data;

class Index extends Action 
{

    protected $_helper;

    public function __construct(
        Context $context,
        Data $helper
    )
    {
        parent::__construct($context);
        $this->_helper = $helper;
    
    }

    public function execute() {
        
        var_dump($this->_helper->getModalTitle());

        // a rota vai ser frontName/diretorio/nomeDaClasse ex: newletter/modal/index
    }
}