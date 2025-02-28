<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

use Magento\Framework\Data\OptionSourceInterface;

class MatPattern extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => 'Linen', 'value' => 'linen'],
                ['label' => 'Suede', 'value' => 'suede'],
                ['label' => 'Paper', 'value' => 'paper']
            ];
        }
        return $this->_options;
    }

}
