<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class MatType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => 'Acid Free - Archival', 'value' => '0'],
                ['label' => 'Linen', 'value' => '1'],
                ['label' => 'Paper - General Purpose', 'value' => '2'],
                ['label' => 'Printed Border', 'value' => '3'],
                ['label' => 'Suede', 'value' => '4']
            ];
        }
        return $this->_options;
    }
}
