<?php
declare(strict_types=1);

namespace Ziffity\Netterms\Controller\Adminhtml\Netterms;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Controller for netterms balance reimbursement from backend.
 */
class Reimburse extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param LoggerInterface $logger
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {

        $result = $this->jsonFactory->create();
        try {
            $customerId = (int)$this->getRequest()->getParam('id');
            $amountSettled = (float)$this->getRequest()->getParam('amount');

            $customer = $this->customerRepository->getById($customerId);
            $nettermsCredit = 0.0;
            if ($customer->getCustomAttribute('po_credit')) {
                $nettermsCredit = (float)$customer->getCustomAttribute('po_credit')->getValue();
            }
            if ($nettermsCredit <= 0 ) {
                throw new LocalizedException(__("This customer doesn't have any outstanding credit to be reimbursed."));
            }
            if ($amountSettled <= 0 || ($amountSettled > $nettermsCredit)) {
                throw new LocalizedException(__("Please enter a correct amount."));
            }
            $updatedNettermsCredit = $nettermsCredit - $amountSettled;
            $customer->setCustomAttribute('po_credit', $updatedNettermsCredit);
            $this->customerRepository->save($customer);
            $result->setData(
                [
                    'error' => false
                ]
            );
            $this->messageManager->addSuccessMessage(__('Successfully updated.'));
        } catch (LocalizedException $e) {
            $result->setData(
                [
                    'error' => true,
                    'error' => $e->getMessage()
                ]
            );
        } catch (\Exception $e) {
            $result->setData(
                [
                    'error' => true,
                    'error' => __('Something went wrong. Please try again later.')
                ]
            );
            $this->logger->critical($e);
        }
        return $result;
    }
}
