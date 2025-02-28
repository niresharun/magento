<?php

namespace Ziffity\Shipping\Controller\Adminhtml\Oversize\Profile;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

class Add extends Action
{

    /**
     * Class Add execute method.
     *
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        return $this->resultRedirectFactory->create()
            ->setPath('shipping/oversize_profile/edit');
    }
}
