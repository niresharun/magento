<?php

namespace Ziffity\AbandonedQuote\Model\ResourceModel\QuoteAbandoned;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Ziffity\AbandonedQuote\Model\QuoteAbandoned::class, \Ziffity\AbandonedQuote\Model\ResourceModel\QuoteAbandoned::class);
    }
}
