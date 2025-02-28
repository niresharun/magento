<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class MatColor extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => 'Black', 'value' => '0'],
                ['label' => 'Blue', 'value' => '1'],
                ['label' => 'Brown', 'value' => '2'],
                ['label' => 'Grey', 'value' => '3'],
                ['label' => 'Golden', 'value' => '4'],
                ['label' => 'Metallic', 'value' => '5'],
                ['label' => 'Silver', 'value' => '6'],
                ['label' => 'Red', 'value' => '7'],
                ['label' => 'Pink', 'value' => '8'],
                ['label' => 'Yellow', 'value' => '9'],
                ['label' => 'White', 'value' => '10']
            ];
        }
        return $this->_options;
    }

}
