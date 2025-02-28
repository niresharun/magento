<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

use Magento\Framework\Data\OptionSourceInterface;

class OpeningType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * Retrieve all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('Single'), 'value' => 'single'],
                ['label' => __('Multiple'), 'value' => 'multiple']
            ];
        }
        return $this->_options;
    }
}
