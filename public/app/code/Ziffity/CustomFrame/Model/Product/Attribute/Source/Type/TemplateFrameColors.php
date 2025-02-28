<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class TemplateFrameColors extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => 'Black', 'value' => '0'],
                ['label' => 'Blue', 'value' => '1'],
                ['label' => 'Bronze (Brown)', 'value' => '2'],
                ['label' => 'Champagne', 'value' => '3'],
                ['label' => 'Charcoal', 'value' => '4'],
                ['label' => 'Cherry', 'value' => '5'],
                ['label' => 'Coffee Brown', 'value' => '6'],
                ['label' => 'Gold', 'value' => '7'],
                ['label' => 'Green', 'value' => '8'],
                ['label' => 'Honey Pecan', 'value' => '9'],
                ['label' => 'Mahogany', 'value' => '10'],
                ['label' => 'Natural Clear', 'value' => '11'],
                ['label' => 'Polished Gold', 'value' => '12'],
                ['label' => 'Polished Silver', 'value' => '13'],
                ['label' => 'Red', 'value' => '14'],
                ['label' => 'Satin Silver', 'value' => '15'],
                ['label' => 'Walnut', 'value' => '16'],
                ['label' => 'White', 'value' => '17'],
                ['label' => 'Wood Faux - Cherry', 'value' => '18'],
                ['label' => 'Wood Faux - Maple', 'value' => '19'],
                ['label' => 'Wood Faux - Oak', 'value' => '20']
            ];
        }
        return $this->_options;
    }
}
