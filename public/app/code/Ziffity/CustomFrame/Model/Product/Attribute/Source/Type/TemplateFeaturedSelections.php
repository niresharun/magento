<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class TemplateFeaturedSelections extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => 'Most Popular', 'value' => '0'],
                ['label' => 'On Sale', 'value' => '1'],
                ['label' => 'Clearance', 'value' => '2']
            ];
        }
        return $this->_options;
    }
}
