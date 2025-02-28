<?php

namespace Ziffity\RequestQuote\Block\Adminhtml;

use Magento\Framework\App\Request\Http;
use Amasty\RequestQuote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Ziffity\RequestQuote\Model\ResourceModel\QuoteComment\CollectionFactory;

/**
 * Order history block
 *
 * @api
 * @since 100.0.2
 */
class AdminComments extends \Magento\Backend\Block\Template
{
    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\Session
     */
    private $quoteSession;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var \Amasty\RequestQuote\Model\ResourceModel\Quote\CollectionFactory
     */
    private $quoteFactory;

    /**
     * @var \Ziffity\RequestQuote\Model\ResourceModel\QuoteComment\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Ziffity\RequestQuote\Model\ResourceModel\QuoteComment\CollectionFactory $collectionFactory
     * @param \Amasty\RequestQuote\Model\ResourceModel\Quote\CollectionFactory $quoteFactory
     * @param \Amasty\RequestQuote\Model\Quote\Backend\Session $quoteSession
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory,
     * @param \Amasty\Base\Model\Serializer $serializer,
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Http $request,
        CollectionFactory $collectionFactory,
        QuoteCollectionFactory $quoteFactory,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $quoteSession,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Amasty\Base\Model\Serializer $serializer,
        array $data = []
    ) {
        $this->request = $request;
        $this->collectionFactory = $collectionFactory;
        $this->quoteFactory = $quoteFactory;
        $this->quoteSession = $quoteSession;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $onclick = "submitAndReloadArea($('order_history_block').parentNode, '" . $this->getSubmitUrl() . "')";
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            ['label' => __('Submit Comment'), 'class' => 'action-save action-secondary', 'onclick' => $onclick]
        );
        $this->setChild('submit_button', $button);
        return parent::_prepareLayout();
    }

    public function getQuoteId()
    {
        $quoteId = $this->request->getParam('quote_id');
        return $quoteId;
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('requestquote/quote/savecomments', ['quote_id' => $this->getQuoteId()]);
    }
    public function getFullHistory()
    {
        $quoteId = $this->getQuoteId();
        $collection  = $this->collectionFactory->create()->addFieldToFilter("quote_id", $quoteId);
        return $collection->setOrder('id', 'DESC');
    }
    public function getStatusOfQuote()
    {
        $quoteId = $this->getQuoteId();
        $collection  = $this->quoteFactory->create()->addFieldToFilter("quote_id", $quoteId);
        return $collection;
    }
    /**
     * @return \Amasty\RequestQuote\Api\Data\QuoteInterface
     */
    public function getQuote()
    {
        return $this->quoteSession->getQuote();
    }

    /**
     * @return \Amasty\RequestQuote\Model\Quote\Backend\Session
     */
    public function getQuoteSession()
    {
        return $this->quoteSession;
    }

    /**
     * Check allow to add comment
     *
     * @return bool
     */
    public function canAddComment()
    {
        return !$this->getNotes()->getAdminNote();
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getNotes()
    {
        if (!$this->getData('notes')) {
            if ($remarks = $this->getQuote()->getRemarks()) {
                $remarks = $this->serializer->unserialize($remarks);
                $this->setData('notes', $this->dataObjectFactory->create(['data' => $remarks]));
            } else {
                $this->setData('notes', $this->dataObjectFactory->create());
            }
        }
        return $this->getData('notes');
    }
}
