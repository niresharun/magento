<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class TemplateLetterBoardMaterial extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => 'Felt', 'value' => '0'],
                ['label' => 'Vinyl', 'value' => '1']
            ];
        }
        return $this->_options;
    }
}
