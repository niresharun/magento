<?php
namespace Ziffity\Netterms\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\Logger;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class Netterms
 */
class Netterms extends AbstractMethod
{
    public const PAYMENT_METHOD_NETTERMS_CODE = 'netterms';

    /**
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_NETTERMS_CODE;

    /**
     * @var string
     */
    protected $_infoBlockType = \Ziffity\Netterms\Block\Info\Netterms::class;

    /**
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->customerRepository = $customerRepository;
    }

    /**
     * Assign data to info model instance
     *
     * @param \Magento\Framework\DataObject|mixed $data
     * @return $this
     * @throws LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo->getQuote()) {
            $customerId = $paymentInfo->getQuote()->getCustomerId();
            if ($customerId) {
                $customer = $this->customerRepository->getById($customerId);
                $isApproved = 0;
                if ($customer->getCustomAttribute('net_terms_approved')) {
                    $isApproved = (float)$customer->getCustomAttribute('net_terms_approved')->getValue();
                }
                $this->getInfoInstance()->setAdditionalInformation('is_customer_approved', $isApproved);
            }
        }
        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws LocalizedException
     */
    public function validate()
    {
        parent::validate();
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo->getQuote()) {
            $customerId = $paymentInfo->getQuote()->getCustomerId();
            if ($customerId) {
                $customer = $this->customerRepository->getById($customerId);

                if ( $customer->getExtensionAttributes() !== null &&
                    $customer->getExtensionAttributes()->getCompanyAttributes() !== null &&
                    $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()) {
                    throw new LocalizedException(
                        __('Company user are not allowed to use the selected payment method.')
                    );
                }

                $canValidateLimit = $this->getConfigData('display_limit_exceed_error_message');
                if ($canValidateLimit) {
                    $nettermsLimit = (float)$this->getConfigData('default_limit');
                    if ($customer->getCustomAttribute('po_limit')) {
                        $nettermsLimitAttribute = (float)$customer->getCustomAttribute('po_limit')->getValue();
                        if ($nettermsLimitAttribute > 0) {
                            $nettermsLimit = $nettermsLimitAttribute;
                        }
                    }
                    $nettermsCredit = 0;
                    if ($customer->getCustomAttribute('po_credit')) {
                        $nettermsCredit = (float)$customer->getCustomAttribute('po_credit')->getValue();
                    }
                    if ($nettermsLimit > 0) {
                        $grandTotal = (float)$paymentInfo->getQuote()->getGrandTotal();
                        $creditTotal = $nettermsCredit + $grandTotal;
                        if ($creditTotal > $nettermsLimit) {
                            throw new LocalizedException(
                                __($this->getConfigData('limit_exceed_message'))
                            );
                        }
                    }
                }

            }
        }
        return $this;
    }
}
