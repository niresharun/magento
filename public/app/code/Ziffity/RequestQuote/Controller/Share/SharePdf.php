<?php
namespace Ziffity\RequestQuote\Controller\Share;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Amasty\RequestQuote\Model\Pdf\PdfProvider;
use Amasty\RequestQuote\Model\Email\TransportBuilder;
use Magento\Framework\View\LayoutInterface;
use Amasty\RequestQuote\Model\UrlResolver;
use Amasty\Base\Model\Serializer;
use Amasty\RequestQuote\Api\Data\QuoteInterface;
use IntlDateFormatter;
use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Registry;
use Amasty\RequestQuote\Model\RegistryConstants;

class SharePdf extends \Magento\Framework\App\Action\Action
{
	public const SHARE_PDF_TEMPLATE_ID = 'amasty_request_quote/sharepdf/share_pdf_template';
	public const SHARE_PDF_EMAIL_SENDER = 'amasty_request_quote/sharepdf/senderemail';
	public const SHARE_PDF_SENDER_NAME = 'amasty_request_quote/sharepdf/sendername';

    /**
     * @var \Amasty\RequestQuote\Model\ResourceModel\Quote\Collection
     */
	protected $quoteCollection;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

	/**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var PdfProvider
     */
    protected $pdfProvider;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var Emulation
     */
    protected $storeEmulation;

        /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

        /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var UrlResolver
     */
    protected $urlResolver;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var QuoteRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amasty\RequestQuote\Model\ResourceModel\Quote\Collection $quoteCollection,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        Session $customerSession,
        PdfProvider $pdfProvider,
        LayoutInterface $layout,
        Emulation $storeEmulation,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        LoggerInterface $logger,
        UrlResolver $urlResolver,
        Serializer $serializer,
        QuoteRepositoryInterface $quoteRepository,
        Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ){
        $this->quoteCollection = $quoteCollection;
        $this->resultFactory = $resultFactory;
        $this->_messageManager = $messageManager;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerSession = $customerSession;
        $this->pdfProvider = $pdfProvider;
        $this->layout = $layout;
        $this->storeEmulation = $storeEmulation;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->urlResolver = $urlResolver;
        $this->serializer = $serializer;
        $this->registry = $registry;
        $this->quoteRepository = $quoteRepository;
        $this->scopeConfig = $scopeConfig;
        return parent::__construct($context);
    }

	public function execute()
	{
		$redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
		if (!$this->formKeyValidator->validate($this->getRequest())) {
			$this->_messageManager->addErrorMessage(__('Invalid form key please refresh the page and try again.'));
		    return $redirect->setUrl($this->_redirect->getRefererUrl());
		}
		$params = $this->_request->getParams();
		try {
			if(array_key_exists('quote_id', $params)) {
                $quoteId = $this->_request->getParam('quote_id', '');
                if($this->sendEmail($quoteId, $params)) {
		        	$this->_messageManager->addSuccess('Successfully shared the quote.');
		        } else {
                    $this->_messageManager->addErrorMessage(__('We can\'t share the quote.'));
		        }
            } else {
                $this->_messageManager->addErrorMessage(__('We can\'t share the quote.'));
			}
	    } catch (Exception $e) {
	    	$this->_messageManager->addErrorMessage(__('We can\'t share the quote.'));
    	}

	    return $redirect->setUrl($this->_redirect->getRefererUrl());
	}

	public function sendEmail($quoteId, $params)
    {
		$amastyQuote = $this->quoteCollection->addFieldToFilter('quote_id', $quoteId)->getFirstItem();
		$quote = $this->quoteRepository->get($quoteId, ['*']);
        $headerType = $params['share-pdf'];
        if ($quote->getId()) {
            $quote->setData('header',$headerType);
//            $this->registry->unregister(RegistryConstants::AMASTY_QUOTE);
            $this->registry->register(RegistryConstants::AMASTY_QUOTE, $quote);
        }
		$this->storeEmulation->startEnvironmentEmulation($amastyQuote->getStoreId());
		$store = $this->storeManager->getStore();
		$defaultData = [
	        'store' => $store,
	        'customerName' => $this->getCustomerSession()->getCustomer()->getName()
	    ];

	    $mailTemplateId = '';
	    $amastyQuote->setHeader('true');
	    $data = [
            'viewUrl' => $this->urlResolver->getViewUrl((int) $amastyQuote->getId(), ['_nosid' => true]),
            'quote' => $amastyQuote,
            'customerName' => $amastyQuote->getCustomerName(),
            'store' => $amastyQuote->getStore(),
            'expiredDate' => '',
            'remarks' => $this->retrieveCustomerNote($amastyQuote->getRemarks()),
            'adminRemarks' => $this->retrieveAdminNote($amastyQuote->getRemarks()),
            'submitted_date' => $amastyQuote->getSubmitedDateFormatted(\IntlDateFormatter::MEDIUM)
        ];

	    try {
	        $transport = $this->transportBuilder->setTemplateIdentifier(
	            $this->getStoreConfig(self::SHARE_PDF_TEMPLATE_ID)
	        )->setTemplateModel(
	            \Amasty\RequestQuote\Model\Email\Template::class
	        )->setTemplateOptions(
	            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store->getId()]
	        )->setTemplateVars(
	            array_merge($defaultData, $data)
	        )->setFrom([
	            'name' => $this->getStoreConfig(self::SHARE_PDF_SENDER_NAME),
	            'email' => $this->getStoreConfig(self::SHARE_PDF_EMAIL_SENDER)
	        ])->addTo(
	            $params['email']
	        );
	        $this->generatePdfHtml($transport, $data);
	    	$this->storeEmulation->stopEnvironmentEmulation();
    	} catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
        return $this;
	}

	protected function getCustomerSession(): Session
    {
        return $this->customerSession;
    }

    protected function retrieveCustomerNote(?string $remarks): string
    {
        $additionalData = $this->serializer->unserialize($remarks);

        return $additionalData[QuoteInterface::CUSTOMER_NOTE_KEY] ?? '';
    }

    protected function retrieveAdminNote(?string $remarks): string
    {
        $additionalData = $this->serializer->unserialize($remarks);

        return $additionalData[QuoteInterface::ADMIN_NOTE_KEY] ?? '';
    }

    public function getStoreConfig($path)
	{
	    return $this->scopeConfig->getValue(
	        $path,
	        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
	    );
	}

    protected function generatePdfHtml($transport, $data)
    {
        $this->layout->getUpdate()->load('amasty_quote_quote_pdf');
        $this->layout->generateXml();
        $this->layout->generateElements();
        $pdfText = $this->pdfProvider->generatePdfText();
        $transport->addAttachment($pdfText, $data['quote']->getIncrementId());
        return $transport->getTransport()->sendMessage();
    }
}
