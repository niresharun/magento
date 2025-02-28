<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class TemplateOutdoorApplication extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => 'Weather Resistant', 'value' => '0']
            ];
        }
        return $this->_options;
    }
}
