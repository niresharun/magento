<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class BoardMaterial extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * Retrieve all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => ' ', 'value' => ''],
                ['label' => 'Vinyl', 'value' => '0'],
                ['label' => 'Felt', 'value' => '1'],
            ];
        }
        return $this->_options;
    }
}
