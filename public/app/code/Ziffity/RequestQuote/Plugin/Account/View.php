<?php

namespace Ziffity\RequestQuote\Plugin\Account;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class View extends \Amasty\RequestQuote\Controller\Account\View
{
	/**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Ziffity\RequestQuote\Model\ResourceModel\Quote\Collection
     */
    protected $quoteCollection;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * AbstractAction constructor.
     *
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Ziffity\RequestQuote\Model\ResourceModel\Quote\Collection $quoteCollection
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Request\Http $request,
        \Ziffity\RequestQuote\Model\ResourceModel\Quote\Collection $quoteCollection,
        \Magento\Framework\Message\ManagerInterface $messageManager,
    ) {
        $this->resultFactory = $resultFactory;
        $this->url = $url;
        $this->request = $request;
        $this->quoteCollection = $quoteCollection;
        $this->_messageManager = $messageManager;
    }
    /**
     * @return \Amasty\RequestQuote\Controller\Account\View $result
     */
    public function afterExecute(\Amasty\RequestQuote\Controller\Account\View $subject, $result)
    {
    	$params = $this->request->getParams();
    	$archiveStatus = 0;
    	if(array_key_exists('quote_id', $params)) {
    		$quoteId = $params['quote_id'];
	    	$amastyQuote = $this->quoteCollection->addFieldToFilter('quote_id', $quoteId)->getFirstItem();
	        $archiveStatus = $amastyQuote->getData('archive');
	    }

    	if ($archiveStatus) {
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

            $this->_messageManager->addErrorMessage(__('Your Quote has been archived. Please contact us to unarchive your quote.'));
            $result = $resultRedirect->setUrl($this->url->getUrl('amasty_quote/account/index'));
        }

        return $result;
    }
}
