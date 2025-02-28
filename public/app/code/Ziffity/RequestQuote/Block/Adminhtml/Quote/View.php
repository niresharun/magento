<?php

namespace Ziffity\RequestQuote\Block\Adminhtml\Quote;

use Amasty\RequestQuote\Api\Data\QuoteInterface;

class View extends \Amasty\RequestQuote\Block\Adminhtml\Quote\View
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Amasty_RequestQuote';

    /**
     * @var \Amasty\RequestQuote\Model\Quote\Backend\Session
     */
    private $quoteSession;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Ziffity\RequestQuote\Model\ResourceModel\Quote\Collection
     */
    protected $quoteFactory;


    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $quoteSession,
        \Magento\Framework\App\Request\Http $request,
        \Ziffity\RequestQuote\Model\ResourceModel\Quote\Collection $quoteFactory,
        array $data = []
    ) {
        $this->quoteSession = $quoteSession;
        $this->request = $request;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($context, $quoteSession, $data);
    }

    protected function _construct()
    {
        parent::_construct();


        $requestParams = $this->request->getParams();
        $quote = $this->getQuote();
        $archiveStatus = 0;
        if(array_key_exists('quote_id', $requestParams)){
            $quoteId = $requestParams['quote_id'];
            $amastyQuote = $this->quoteFactory->addFieldToFilter('quote_id', $quoteId)->getFirstItem();
            $archiveStatus = $amastyQuote->getData('archive');
        }

        if ($this->_isAllowedAction('Amasty_RequestQuote::approve') && !$archiveStatus) {
            $this->addButton(
                'quote_archive',
                [
                    'label' => __('Archive'),
                    'class' => 'quote-action-button',
                    'id' => 'quote-view-archive-button',
                    'data_attribute' => [
                        'url' => $this->getArchiveUrl(),
                        'amquote-js' => 'archive'
                    ]
                ]
            );
        } elseif ($this->_isAllowedAction('Amasty_RequestQuote::approve') && $archiveStatus) {
            $this->addButton(
                'quote_archive',
                [
                    'label' => __('Un Archive'),
                    'class' => 'quote-action-button',
                    'id' => 'quote-view-archive-button',
                    'data_attribute' => [
                        'url' => $this->getArchiveUrl(),
                        'amquote-js' => 'unarchive'
                    ]
                ]
            );
        }

        if($archiveStatus) {
            $this->removeButton('quote_edit');
            $this->removeButton('pdf_download');
            $this->removeButton('quote_order');
            $this->removeButton('quote_approve');
            $this->removeButton('quote_close');
        }
    }

    /**
     * @return string
     */
    public function getArchiveUrl()
    {
        return $this->getUrl('requestquote/archive/index');
    }

}
