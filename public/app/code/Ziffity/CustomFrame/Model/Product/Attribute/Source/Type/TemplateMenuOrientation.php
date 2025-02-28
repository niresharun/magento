<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class TemplateMenuOrientation extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => 'Menu - Portrait', 'value' => '0'],
                ['label' => 'Menu - Landscape', 'value' => '1']
            ];
        }
        return $this->_options;
    }
}
