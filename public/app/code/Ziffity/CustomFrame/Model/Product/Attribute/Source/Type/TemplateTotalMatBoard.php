<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class TemplateTotalMatBoard extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => 'Single Mat', 'value' => '0'],
                ['label' => 'Double Mat', 'value' => '1'],
                ['label' => 'Triple Mat', 'value' => '2']
            ];
        }
        return $this->_options;
    }
}
