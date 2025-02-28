<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class TemplateWithLights extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => 'No - Lights', 'value' => '0'],
                ['label' => 'Yes - Lights', 'value' => '1']
            ];
        }
        return $this->_options;
    }
}
