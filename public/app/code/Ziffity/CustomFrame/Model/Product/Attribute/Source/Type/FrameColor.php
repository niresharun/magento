<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class FrameColor extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @return array|null
     */
    public function getAllOptions()
    {
        $materials = [
            'Black',
            'Blue',
            'Brown',
            'Carbon Steel',
            'Cream / Beige',
            'Copper / Bronze',
            'Green',
            'Gold',
            'Grey',
            'Natural Clear',
            'Orange',
            'Pink',
            'Purple',
            'Red',
            'Silver',
            'White',
            'Yellow',
        ];
        if (!$this->_options && $this->_options === null) {
            foreach ($materials as $key=>$material) {
                $this->_options[] = [
                    'value' => $key,
                    'label' => $material,
                ];
            }
        }
        return $this->_options;
    }
}
