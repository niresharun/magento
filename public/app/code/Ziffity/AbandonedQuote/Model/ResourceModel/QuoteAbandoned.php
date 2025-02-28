<?php
namespace Ziffity\AbandonedQuote\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class QuoteAbandoned extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('abandoned_quote_schedule', 'id');
    }
}
