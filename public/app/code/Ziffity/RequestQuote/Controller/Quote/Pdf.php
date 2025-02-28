<?php

declare(strict_types=1);

namespace Ziffity\RequestQuote\Controller\Quote;

use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Pdf\ComponentChecker;
use Amasty\RequestQuote\Model\Pdf\PdfProvider;
use Amasty\RequestQuote\Model\Quote;
use Amasty\RequestQuote\Model\Registry;
use Amasty\RequestQuote\Model\RegistryConstants;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session as CustomerSession;

class Pdf extends \Amasty\RequestQuote\Controller\Quote\Pdf
{
    /**
     * @var PdfProvider
     */
    private $pdfProvider;

    /**
     * @var ComponentChecker
     */
    private $componentChecker;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    public function __construct(
        Context $context,
        PdfProvider $pdfProvider,
        ComponentChecker $componentChecker,
        LoggerInterface $logger,
        QuoteRepositoryInterface $quoteRepository,
        CustomerSession $customerSession,
        Registry $registry
    ) {
        parent::__construct($context, $pdfProvider, $componentChecker, $logger, $quoteRepository, $customerSession, $registry);
        $this->pdfProvider = $pdfProvider;
        $this->componentChecker = $componentChecker;
        $this->quoteRepository = $quoteRepository;
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        if (!$this->componentChecker->isComponentsExist()) {
            $this->messageManager->addErrorMessage($this->componentChecker->getComponentsErrorMessage());

            return $this->_redirect('*/*/');
        }

        try {
            $quoteId = (int)$this->_request->getParam('quote_id');
            $quote = $this->quoteRepository->get($quoteId);
            $headerType = $this->_request->getParam('header');
            $quote->setData('header',$headerType);
            $this->validateCustomer($quote);
            $this->registry->register(RegistryConstants::AMASTY_QUOTE, $quote);
            $this->_view->loadLayout();

            return $this->pdfProvider->getRawPdf($quote);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred. The PDF was not downloaded.'));
            $this->logger->error($e->getMessage());
        }

        $this->_redirect('*/account/');
    }

    /**
     * @param Quote $quote
     * @throws \LocalizedException
     */
    private function validateCustomer(Quote $quote): void
    {
        $customer = $this->customerSession->getCustomer();
        if ($customer->getId() && $quote->getCustomerId() !== $customer->getId()) {
            throw new LocalizedException(__('Requested quote does not exist for current customer'));
        }
    }
}
