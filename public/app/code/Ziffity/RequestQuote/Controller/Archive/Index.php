<?php
namespace Ziffity\RequestQuote\Controller\Archive;

class Index extends \Magento\Framework\App\Action\Action
{

	/**
	* @var \Amasty\RequestQuote\Model\QuoteFactory
	*/
	protected $_quoteModel;

	/**
	* @var \Ziffity\RequestQuote\Model\ResourceModel\Quote
	*/
	protected $_quoteRepo;

	/**
	* @var \Magento\Framework\Controller\ResultFactory
	*/
	protected $resultFactory;

	/**
	* @var \Magento\Framework\Message\ManagerInterface
	*/
	protected $_messageManager;

	/**
	* @var \Magento\Framework\Data\Form\FormKey\Validato
	*/
	protected $formKeyValidator;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Amasty\RequestQuote\Model\QuoteFactory $quoteModel,
		\Ziffity\RequestQuote\Model\ResourceModel\Quote $quoteRepo,
		\Magento\Framework\Controller\ResultFactory $resultFactory,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
	){
		$this->_quoteModel = $quoteModel;
		$this->_quoteRepo = $quoteRepo;
		$this->resultFactory = $resultFactory;
		$this->_messageManager = $messageManager;
		$this->formKeyValidator = $formKeyValidator;
		return parent::__construct($context);
	}

	public function execute()
	{
        $params = $this->_request->getParams();

        $quoteId = '';
        if(array_key_exists('quoteid', $params)) {
            $quoteId = $this->_request->getParam('quoteid', '');
        }
		$redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

		if (!$this->formKeyValidator->validate($this->getRequest()) && $quoteId) {
			$this->_messageManager->addErrorMessage(__('Invalid form key please refresh the page and try again.'));
			return $redirect->setUrl('/quote/account/index');
		}


		try {
			if($quoteId) {
				$quoteModel = $this->_quoteModel->create();
				$this->_quoteRepo->load($quoteModel, $quoteId);
				$quoteModel = $quoteModel->setArchive(1);
                $this->_quoteRepo->save($quoteModel);
				$this->_messageManager->addSuccess('Successfully archived the quote.');
			} else {
				$this->_messageManager->addErrorMessage(__('We can\'t archive the quote.'));
			}
		} catch (Exception $e) {
			$this->_messageManager->addErrorMessage(__('We can\'t archive the quote.'));
		}

		return $redirect->setUrl('/quote/account/index');
	}
}
