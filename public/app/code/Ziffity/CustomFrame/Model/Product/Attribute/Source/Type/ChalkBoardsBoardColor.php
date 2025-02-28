<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class ChalkBoardsBoardColor extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => ' ', 'value' => ''],
                ['label' => 'Black', 'value' => '0'],
                ['label' => 'Red', 'value' => '1'],
                ['label' => 'White', 'value' => '2'],
                ['label' => 'Grey', 'value' => '3'],
                ['label' => 'Blue', 'value' => '4'],
                ['label' => 'Brown', 'value' => '5'],
                ['label' => 'Burgundy', 'value' => '6'],
                ['label' => 'Green', 'value' => '7'],
                ['label' => 'Pink', 'value' => '8'],
                ['label' => 'Yellow', 'value' => '9']
            ];
        }
        return $this->_options;
    }
}
