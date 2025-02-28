<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class FabricColor extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => ' ', 'value' => ''],
                ['label' => 'Black', 'value' => '0'],
                ['label' => 'Blue', 'value' => '1'],
                ['label' => 'Brown', 'value' => '2'],
                ['label' => 'Green', 'value' => '3'],
                ['label' => 'Grey', 'value' => '4'],
                ['label' => 'Orange', 'value' => '5'],
                ['label' => 'Pink', 'value' => '6'],
                ['label' => 'Purple', 'value' => '7'],
                ['label' => 'Red', 'value' => '8'],
                ['label' => 'Tan', 'value' => '9'],
                ['label' => 'Teal', 'value' => '10'],
                ['label' => 'White', 'value' => '11'],
                ['label' => 'Yellow', 'value' => '12'],
            ];
        }
        return $this->_options;
    }
}
