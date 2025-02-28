<?php
namespace Ziffity\RequestQuote\Controller\Adminhtml\Archive;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;

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
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Amasty\RequestQuote\Model\ResourceModel\Quote\Collection
     */
    protected $quoteCollection;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Amasty\RequestQuote\Model\QuoteFactory $quoteModel,
        \Ziffity\RequestQuote\Model\ResourceModel\Quote $quoteRepo,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Amasty\RequestQuote\Model\ResourceModel\Quote\Collection $quoteCollection,
    ){
        $this->_pageFactory = $pageFactory;
        $this->_quoteModel = $quoteModel;
        $this->_quoteRepo = $quoteRepo;
        $this->resultFactory = $resultFactory;
        $this->_messageManager = $messageManager;
        $this->formKeyValidator = $formKeyValidator;
        $this->quoteCollection = $quoteCollection;
        return parent::__construct($context);
    }
    /**
     * Archive controller page.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $quoteId = '';
        if(array_key_exists('quote_id', $params)) {
            $quoteId = $this->getRequest()->getParam('quote_id', '');
        }

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->_messageManager->addErrorMessage(__('Invalid form key please refresh the page and try again.'));
            return $resultRedirect->setPath('amasty_quote/quote/view/', ['quote_id' => $quoteId]);
        }

        try {
            if($quoteId) {
                $quoteModel = $this->_quoteModel->create();
                $this->_quoteRepo->load($quoteModel, $quoteId);
                $amastyQuote = $this->quoteCollection->addFieldToFilter('quote_id', $quoteId)->getFirstItem();
                $archiveStatus = $amastyQuote->getData('archive');
                if($archiveStatus){
                    $quoteModel->setArchive(0);
                    $this->_messageManager->addSuccess('Successfully unarchived the quote.');
                } else {
                    $quoteModel->setArchive(1);
                    $this->_messageManager->addSuccess('Successfully archived the quote.');
                }
                $this->_quoteRepo->save($quoteModel);
            } else {
                $this->_messageManager->addErrorMessage(__('We can\'t archive the quote.'));
            }
        } catch (Exception $e) {
            $this->_messageManager->addErrorMessage(__('We can\'t archive the quote.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('amasty_quote/quote/view/', ['quote_id' => $quoteId]);
        return $resultRedirect;

    }

    /**
     * Check Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ziffity_RequestQuote::stores_settings');
    }
}
