<?php

namespace Ziffity\Netterms\Block\Adminhtml\Customer\Edit;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

class NettermsCreditBalance extends \Magento\Backend\Block\Template
{

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CustomerRepositoryInterface $customerRepository
     * @param PriceHelper $priceHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CustomerRepositoryInterface $customerRepository,
        PriceHelper $priceHelper,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->customerRepository = $customerRepository;
        $this->priceHelper = $priceHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get credit outstanding balance.
     *
     * @return int
     */
    public function getCustomerCreditBalance()
    {
        $nettermsCredit = 0;
        if ($this->getCustomerId()) {
            $customer = $this->customerRepository->getById($this->getCustomerId());
            if ($customer->getCustomAttribute('po_credit')) {
                $nettermsCredit = (float)$customer->getCustomAttribute('po_credit')->getValue();
            }
        }
        $formattednettermsCredit = $this->priceHelper->currency($nettermsCredit, true, false);
        return  $formattednettermsCredit;
    }

    /**
     * Get customer ID
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }
}
