<?php

namespace Ziffity\RequestQuote\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class Archive implements ArgumentInterface
{

	protected $quoteCollection;

    public function __construct(
    	\Amasty\RequestQuote\Model\ResourceModel\Quote\Collection $quoteCollection
    ){
    	$this->quoteCollection = $quoteCollection;
    }

    public function getArchiveStatus($quoteId)
    {
        $amastyQuote = $this->quoteCollection->addFieldToFilter('quote_id', $quoteId)->getFirstItem();

        return $amastyQuote->getData('archive');
    }
}
