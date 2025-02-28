<?php

namespace Ziffity\RequestQuote\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;

class saveQuoteName implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct(
        RequestInterface $request,
    ) {
        $this->request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        $quoteName = $this->request->getParam('quote_name');
        $quote->setQuoteName($quoteName);
    }
}
