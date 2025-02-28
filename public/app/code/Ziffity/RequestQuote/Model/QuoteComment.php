<?php

namespace Ziffity\RequestQuote\Model;

use Magento\Framework\Model\AbstractModel;

class QuoteComment extends AbstractModel
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Ziffity\RequestQuote\Model\ResourceModel\QuoteComment::class);
    }
}
