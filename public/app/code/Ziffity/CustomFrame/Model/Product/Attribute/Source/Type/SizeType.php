<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class SizeType extends AbstractSource
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
                [
                    'label' => __('Frame'),
                    'value' => '1',
                ],
                [
                    'label' => __('Graphic'),
                    'value' => '2',
                ]
            ];
        }
        return $this->_options;
    }
}
