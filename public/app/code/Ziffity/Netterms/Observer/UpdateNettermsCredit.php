<?php
declare(strict_types=1);

namespace Ziffity\Netterms\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Update the Netterms credit for customer when order was placed successful.
 */
class UpdateNettermsCredit implements ObserverInterface
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
     * @param LoggerInterface $logger
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
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

                if ($customerId) {
                    $customer = $this->customerRepository->getById($customerId);
                    $nettermsCredit = 0;
                    if ($customer->getCustomAttribute('po_credit')) {
                        $nettermsCredit = (float)$customer->getCustomAttribute('po_credit')->getValue();
                    }

                    $grandTotal = (float)$order->getGrandTotal();
                    $updatedNettermsCredit = $nettermsCredit + $grandTotal;
                    $customer->setCustomAttribute('po_credit', $updatedNettermsCredit);
                    $this->customerRepository->save($customer);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
