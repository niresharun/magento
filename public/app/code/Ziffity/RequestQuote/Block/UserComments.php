<?php

namespace Ziffity\RequestQuote\Block;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey;
use Ziffity\RequestQuote\Model\ResourceModel\QuoteComment\CollectionFactory;
use Amasty\RequestQuote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;

class UserComments extends Template
{
    /**
     * Request var contain Http
     *
     * @var $request
     */

    protected $request;

    /**
     *
     * @var $formKey
     */

    protected $formKey;

    /**
     *
     * @var $collectionFactory
     */

    protected $collectionFactory;

    /**
     *
     * @var $quoteFactory
     */

    protected $quoteFactory;


    /**
     * @param Context $context
     * @param Http $request
     * @param FormKey $formKey
     * @param CollectionFactory $collectionFactory
     * @param QuoteCollectionFactory $quoteFactory
     * @param array $data
     */

    public function __construct(
        Context $context,
        Http $request,
        FormKey $formKey,
        CollectionFactory $collectionFactory,
        QuoteCollectionFactory $quoteFactory,
        array $data = []
    ) {
        $this->request = $request;
        $this->formKey = $formKey;
        $this->collectionFactory = $collectionFactory;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($context, $data);
    }

    /**
     * Return the paticular feedback
     */

    public function getQuoteId()
    {
        $quoteId = $this->request->getParam('quote_id');
        return $quoteId;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
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
        return $this->quoteFactory->create()->addFieldToFilter("quote_id", $quoteId);

    }
}
