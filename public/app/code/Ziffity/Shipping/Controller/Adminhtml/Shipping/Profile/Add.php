<?php

namespace Ziffity\Shipping\Controller\Adminhtml\Shipping\Profile;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

class Add extends Action
{
    /**
     * Class Add execute function.
     *
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $redirect = $this->resultRedirectFactory
            ->create();
        return $redirect->setPath('shipping/shipping_profile/edit');
    }
}
