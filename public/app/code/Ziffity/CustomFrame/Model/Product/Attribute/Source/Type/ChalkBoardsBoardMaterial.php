<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class ChalkBoardsBoardMaterial extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => 'Magnetic', 'value' => '0'],
                ['label' => 'Melamine (non-magnetic)', 'value' => '1'],
            ];
        }
        return $this->_options;
    }
}
