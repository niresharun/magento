<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class LaminateColors extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => 'Black', 'value' => '0'],
                ['label' => 'Blue', 'value' => '1'],
                ['label' => 'Brown', 'value' => '2'],
                ['label' => 'Copper / Bronze', 'value' => '3'],
                ['label' => 'Golden', 'value' => '4'],
                ['label' => 'Green', 'value' => '5'],
                ['label' => 'Grey', 'value' => '6'],
                ['label' => 'Metallic', 'value' => '7'],
                ['label' => 'Orange', 'value' => '8'],
                ['label' => 'Pink', 'value' => '9'],
                ['label' => 'Purple', 'value' => '10'],
                ['label' => 'Red', 'value' => '11'],
                ['label' => 'Silver', 'value' => '12'],
                ['label' => 'Tan / Beige', 'value' => '13'],
                ['label' => 'White / Cream', 'value' => '14'],
                ['label' => 'Wood Type - Beech / Ash', 'value' => '15'],
                ['label' => 'Wood Type - Cherry', 'value' => '16'],
                ['label' => 'Wood Type - Mahogany', 'value' => '17'],
                ['label' => 'Wood Type - Maple', 'value' => '18'],
                ['label' => 'Wood Type - Oak', 'value' => '19'],
                ['label' => 'Wood Type - Specialty', 'value' => '20'],
                ['label' => 'Wood Type - Walnut', 'value' => '21'],
                ['label' => 'Yellow', 'value' => '22']
            ];
        }
        return $this->_options;
    }
}
