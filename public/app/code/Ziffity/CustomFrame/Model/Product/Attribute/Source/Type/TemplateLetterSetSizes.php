<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class TemplateLetterSetSizes extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => '1/2"', 'value' => '0'],
                ['label' => '3/4"', 'value' => '1'],
                ['label' => '1"', 'value' => '2'],
                ['label' => '2"', 'value' => '3'],
                ['label' => '3"', 'value' => '4'],
            ];
        }
        return $this->_options;
    }
}
