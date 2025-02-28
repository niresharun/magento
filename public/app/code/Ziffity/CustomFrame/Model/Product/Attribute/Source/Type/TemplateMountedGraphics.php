<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class TemplateMountedGraphics extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => 'No - Mounted', 'value' => '0'],
                ['label' => 'Yes - Mounted', 'value' => '1']
            ];
        }
        return $this->_options;
    }
}
