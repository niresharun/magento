<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class TemplateLetterSetColors extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => 'Brown', 'value' => '2'],
                ['label' => 'Gold', 'value' => '3'],
                ['label' => 'Green', 'value' => '4'],
                ['label' => 'Orange', 'value' => '5'],
                ['label' => 'Pink', 'value' => '6'],
                ['label' => 'Purple', 'value' => '7'],
                ['label' => 'Red', 'value' => '8'],
                ['label' => 'Silver', 'value' => '9'],
                ['label' => 'White', 'value' => '10'],
                ['label' => 'Yellow', 'value' => '11'],
            ];
        }
        return $this->_options;
    }
}
