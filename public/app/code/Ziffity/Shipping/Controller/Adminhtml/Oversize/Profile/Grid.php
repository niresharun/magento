<?php

namespace Ziffity\Shipping\Controller\Adminhtml\Oversize\Profile;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Grid extends Action
{

    public const ADMIN_RESOURCE = 'Ziffity_Shipping::oversize_profile';

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
     * Class Grid execute method.
     *
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $result = $this->pageFactory->create();
        return $result;
    }
}
