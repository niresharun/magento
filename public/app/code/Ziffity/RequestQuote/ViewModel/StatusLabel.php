<?php

namespace Ziffity\RequestQuote\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Ziffity\RequestQuote\Model\Source\AdditionalStatus;

class StatusLabel implements ArgumentInterface
{
    protected AdditionalStatus $status;

    public function __construct(
        AdditionalStatus $status
    ) {
        $this->status = $status;
    }

    public function getLabel($status)
    {
        return $this->status->getStatusLabel($status);
    }
}
