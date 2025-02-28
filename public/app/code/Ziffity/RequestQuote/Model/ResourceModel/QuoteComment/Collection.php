<?php

namespace Ziffity\RequestQuote\Model\ResourceModel\QuoteComment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Ziffity\RequestQuote\Model\QuoteComment::class, \Ziffity\RequestQuote\Model\ResourceModel\QuoteComment::class);
    }
}
