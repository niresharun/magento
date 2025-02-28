<?php

namespace Ziffity\RequestQuote\Model\ResourceModel\Quote;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Source\Status;

class Collection extends \Amasty\RequestQuote\Model\ResourceModel\Quote\Collection
{

    /**
     * @inheritdoc
     */
    protected function _renderFiltersBefore()
    {
        $this->getSelect()->join(
            ['amasty_quote' => $this->getResource()->getAmastyQuoteTable()],
            'amasty_quote.quote_id = main_table.entity_id',
            [
                'status',
                'remarks',
                'increment_id',
                'customer_name',
                'expired_date',
                'reminder_date',
                'submited_date',
                'archive',
                'quote_name',
                QuoteInterface::DISCOUNT,
                QuoteInterface::SURCHARGE
            ]
        );

        // parent::_renderFiltersBefore();
    }
}
