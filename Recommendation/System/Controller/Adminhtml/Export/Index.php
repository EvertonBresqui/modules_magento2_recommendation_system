<?php

namespace Recommendation\System\Controller\Adminhtml\Export;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Recommendation\System\Helper\Data;

class Index extends Action
{
    protected $_resultFactory;
    protected $_store;
    protected $_helper;

    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        StoreManagerInterface $store,
        Data $helper
    )
    {
        parent::__construct($context);
        $this->_resultFactory = $resultFactory;
        $this->_store = $store;
        $this->_helper = $helper;
    }

    public function execute()
    {
        $page = $this->_resultFactory->create(ResultFactory::TYPE_PAGE);
        return $page;
    }
}