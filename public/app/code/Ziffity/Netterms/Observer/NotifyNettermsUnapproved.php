<?php
declare(strict_types=1);

namespace Ziffity\Netterms\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;
use Ziffity\Netterms\Model\Netterms;
use Ziffity\Netterms\Helper\Data;

/**
 * Notify customer to fill attachment for netterms approval.
 */
class NotifyNettermsUnapproved implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @param LoggerInterface $logger
     * @param CustomerRepositoryInterface $customerRepository
     * @param Data $helperData
     */
    public function __construct(
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository,
        Data $helperData
    ) {
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
        $this->helperData = $helperData;
    }

    /**
     * Update the Netterms credit for customer when order was placed successful.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getOrder();
        try {
            if ($order) {
                $customerId = $order->getCustomerId();
                $orderData = [
                    'increment_id' => $order->getIncrementId(),
                    'customer_name' => $order->getCustomerName(),
                    'store_id' => $order->getStore()->getId(),
                ];

                if (!$order->getCustomerIsGuest()) {
                    $customer = $this->customerRepository->getById($customerId);
                    $isApproved = 0;
                    if ($customer->getCustomAttribute('net_terms_approved')) {
                        $isApproved = (int)$customer->getCustomAttribute('net_terms_approved')->getValue();
                    }
                    $payment = $order->getPayment();
                    $method = $payment->getMethodInstance();
                    $methodCode = $method->getCode();
                    if (!$isApproved && $methodCode == Netterms::PAYMENT_METHOD_NETTERMS_CODE) {
                        $this->helperData->sentNonApprovedEmail($order->getCustomerEmail(), $orderData);
                    }
                } else {
                    $this->helperData->sentNonApprovedEmail($order->getCustomerEmail(), $orderData);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
