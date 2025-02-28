<?php

namespace Ziffity\Shipping\Controller\Adminhtml\Shipping\Profile;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Grid extends Action
{

    public const ADMIN_RESOURCE = 'Ziffity_Shipping::shipping_profile';

    /**
     * @var PageFactory
     */
    protected PageFactory $pageFactory;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(Context $context, PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    /**
     * Class Grid execute function
     *
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        return $this->pageFactory->create();
    }
}
