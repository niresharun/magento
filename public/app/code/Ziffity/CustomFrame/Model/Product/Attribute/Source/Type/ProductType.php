<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

use Magento\Framework\Data\OptionSourceInterface;

class ProductType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => 'Wall Mount', 'value' => 'Wall Mount'],
                ['label' => 'Floor Stand', 'value' => 'Floor Stand']
            ];
        }
        return $this->_options;
    }
}
