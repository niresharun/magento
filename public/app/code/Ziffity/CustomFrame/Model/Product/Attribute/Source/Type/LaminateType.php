<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class LaminateType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => 'Wood Finish', 'value' => '0'],
                ['label' => 'Solid Colors', 'value' => '1'],
                ['label' => 'Metallic', 'value' => '2']
            ];
        }
        return $this->_options;
    }
}
