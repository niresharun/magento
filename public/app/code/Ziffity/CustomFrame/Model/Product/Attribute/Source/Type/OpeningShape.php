<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class OpeningShape extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('Rectangle'), 'value' => 'rectangle'],
                ['label' => __('Circle'), 'value' => 'circle']
            ];
        }
        return $this->_options;
    }
}
