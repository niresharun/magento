<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class TemplateFrameWidth extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => '7/8"', 'value' => '0'],
                ['label' => '1"', 'value' => '1'],
                ['label' => '1 3/16"', 'value' => '2'],
                ['label' => '1 1/4"', 'value' => '3'],
                ['label' => '1 3/8"', 'value' => '4'],
                ['label' => '1 5/8"', 'value' => '5'],
                ['label' => '1 3/4"', 'value' => '6'],
                ['label' => '2 1/2"', 'value' => '7']
            ];
        }
        return $this->_options;
    }
}
