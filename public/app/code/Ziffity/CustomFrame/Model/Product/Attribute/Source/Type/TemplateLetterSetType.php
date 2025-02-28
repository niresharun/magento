<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class TemplateLetterSetType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => 'Sprue Set', 'value' => '0'],
                ['label' => 'Boxed Set', 'value' => '1']
            ];
        }
        return $this->_options;
    }
}
