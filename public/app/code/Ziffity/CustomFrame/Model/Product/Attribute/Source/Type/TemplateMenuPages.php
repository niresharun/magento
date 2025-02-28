<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class TemplateMenuPages extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => '1 Menu', 'value' => '0'],
                ['label' => '2 Menus', 'value' => '1'],
                ['label' => '3 Menus', 'value' => '2'],
                ['label' => '4 Menus', 'value' => '3']
            ];
        }
        return $this->_options;
    }
}
