<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class TemplateInteriorDepths extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => '1 inch', 'value' => '0'],
                ['label' => '2 inch', 'value' => '1'],
                ['label' => '3 inch', 'value' => '2'],
                ['label' => '4 inch', 'value' => '3'],
                ['label' => '5 inch', 'value' => '4'],
                ['label' => '6 inch', 'value' => '5'],
                ['label' => '7 inch', 'value' => '6'],
                ['label' => '8 inch', 'value' => '7'],
                ['label' => '9 inch', 'value' => '8'],
                ['label' => '10 inch', 'value' => '9']
            ];
        }
        return $this->_options;
    }
}
