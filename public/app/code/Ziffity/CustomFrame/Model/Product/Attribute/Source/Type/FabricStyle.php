<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class FabricStyle extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => ' ', 'value' => null],
                ['label' => 'Subtle Weave', 'value' => '0'],
                ['label' => 'Birch', 'value' => '1']
            ];
        }
        return $this->_options;
    }
}
