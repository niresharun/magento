<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class MatboardTopFraction extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                [
                    'label' => __('0"'),
                    'value' => '0',
                ],
                [
                    'label' => __('1/4"'),
                    'value' => '1/4',
                ],
                [
                    'label' => __('3/8"'),
                    'value' => '3/8',
                ],
                [
                    'label' => __('1/2"'),
                    'value' => '1/2',
                ],
                [
                    'label' => __('5/8"'),
                    'value' => '5/8',
                ],
                [
                    'label' => __('3/4"'),
                    'value' => '3/4',
                ],
                [
                    'label' => __('7/8"'),
                    'value' => '7/8',
                ]
            ];
        }
        return $this->_options;
    }
}
