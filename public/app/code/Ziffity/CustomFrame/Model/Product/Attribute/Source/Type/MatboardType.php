<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

use Magento\Framework\Data\OptionSourceInterface;

class MatboardType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => 'Yes - Mat', 'value' => 'Yes - Mat'],
                ['label' => 'No - Mat', 'value' => 'No - Mat']
            ];
        }
        return $this->_options;
    }
}
