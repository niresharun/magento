<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class BackingBoardType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __(' '), 'value' => ''],
                ['label' => __('PVC'), 'value' => '0'],
                ['label' => __('Coroplast'), 'value' => '1'],
                ['label' => __('Styrene'), 'value' => '2'],
                ['label' => __('None'), 'value' => '3'],
                ['label' => __('Cardboard'), 'value' => '4'],
                ['label' => __('Acid-Free Foam Board'), 'value' => '5'],
                ['label' => __('Foam Board'), 'value' => '6'],
                ['label' => __('1/4" Melamine'), 'value' => '7'],
                ['label' => __('LED Lighted Edge-Lit Panel'), 'value' => '8'],
                ['label' => __('Metal Slide In Frame'), 'value' => '9'],
            ];
        }
        return $this->_options;
    }

    /**
     * Retrieve all attribute options
     *
     * @return array
     */
    public function getOptionsValue()
    {
        return [
            'PVC',
            'Coroplast',
            'Styrene',
            'None',
            'Cardboard',
            'Acid-Free Foam Board',
            'Foam Board',
            '1/4" Melamine',
            'LED Lighted Edge-Lit Panel',
            'Metal Slide In Frame',
        ];
    }
}
