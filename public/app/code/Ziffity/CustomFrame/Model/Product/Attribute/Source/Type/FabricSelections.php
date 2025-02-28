<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class FabricSelections extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => 'Most Popular', 'value' => '0'],
                ['label' => 'On Sale', 'value' => '1']
            ];
        }
        return $this->_options;
    }
}
