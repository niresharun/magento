<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class TemplateLetterStyle extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => 'Helvetica', 'value' => '0'],
                ['label' => 'Roman', 'value' => '1']
            ];
        }
        return $this->_options;
    }
}
