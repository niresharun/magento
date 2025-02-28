<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

use Magento\Framework\Data\OptionSourceInterface;

class MatOpeningsType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => '1 Opening', 'value' => '1 Opening'],
                ['label' => '2 Openings', 'value' => '2 Openings'],
                ['label' => '3 Openings', 'value' => '3 Openings'],
                ['label' => '4 Openings', 'value' => '4 Openings']
            ];
        }
        return $this->_options;
    }
}
