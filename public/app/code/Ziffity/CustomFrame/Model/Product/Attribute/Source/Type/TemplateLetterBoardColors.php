<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class TemplateLetterBoardColors extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => 'Burgundy', 'value' => '3'],
                ['label' => 'Green', 'value' => '4'],
                ['label' => 'Grey', 'value' => '5'],
                ['label' => 'Pink', 'value' => '6'],
                ['label' => 'White', 'value' => '7'],
                ['label' => 'Yellow', 'value' => '8']
            ];
        }
        return $this->_options;
    }
}
