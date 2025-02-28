<?php

namespace Ziffity\AbandonedQuote\Model;

use Magento\Framework\Model\AbstractModel;

class QuoteAbandoned extends AbstractModel
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Ziffity\AbandonedQuote\Model\ResourceModel\QuoteAbandoned::class);
    }
}