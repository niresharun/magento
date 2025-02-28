<?php

namespace Ziffity\SavedDesigns\Model;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;

class SavedDesignsAuthenticator
{

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @param CustomerSession $customerSession
     */
    public function __construct(
        CustomerSession $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * Check customer allowed to perform action
     *
     * @param int $customerId
     * @return LocalizedException|void
     */
    public function isAllowedAction($customerId)
    {
        $currentCustomerId = $this->customerSession->getCustomerId();
        if ($customerId !== $currentCustomerId) {
            throw new LocalizedException(__('You are not allowed to perform this action.'));
        }
    }
}
