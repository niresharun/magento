<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

use Magento\Framework\Data\OptionSourceInterface;

class OrientationType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => 'Portrait', 'value' => 'Portrait'],
                ['label' => 'Landscape', 'value' => 'Landscape'],
                ['label' => 'Square', 'value' => 'Square']
            ];
        }
        return $this->_options;
    }
}
