<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

use Magento\Framework\Data\OptionSourceInterface;

class PersonalizedLabels extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => 'No - Labels', 'value' => 'No - Labels'],
                ['label' => 'Yes - Labels', 'value' => 'Yes - Labels']
            ];
        }
        return $this->_options;
    }
}
