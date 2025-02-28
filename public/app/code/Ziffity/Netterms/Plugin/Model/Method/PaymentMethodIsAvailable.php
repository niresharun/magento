<?php
namespace Ziffity\Netterms\Plugin\Model\Method;

use Magento\Quote\Api\Data\CartInterface;
use Ziffity\Netterms\Model\Netterms;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class PaymentMethodIsAvailable
 */
class PaymentMethodIsAvailable
{

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * PaymentMethodIsAvailable constructor.
     *
     * @param CustomerSession $customerSession
     */
    public function __construct(
        CustomerSession $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * @param Netterms $subject
     * @param \Closure $proceed
     * @param CartInterface $quote
     * @return bool
     */
    public function aroundIsAvailable(
        Netterms $subject,
        \Closure $proceed,
        CartInterface $quote = null
    ) {
        $customer = null;

        // Admin order creation availability handling
        if ($quote) {
            $customer = $quote->getCustomer();
        }

        // Storefront checkout availability handling
        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomerData();
        }

        // Hide Netterms for company user
        if ($customer && $customer->getExtensionAttributes() !== null &&
            $customer->getExtensionAttributes()->getCompanyAttributes() !== null &&
            $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()) {
            return false;
        }

        return $proceed($quote);
    }
}
