<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class GlassType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => __(' '), 'value' => null],
                ['label' => __('Non Glare .015 PETG'), 'value' => '0'],
                ['label' => __('Premium Clear Acrylic'), 'value' => '1'],
                ['label' => __('Break Resistant Acrylic .093'), 'value' => '2'],
                ['label' => __('Non Glare Acrylic .060'), 'value' => '3'],
                ['label' => __('Tempered Glass'), 'value' => '4'],
                ['label' => __('Clear Acrylic .040'), 'value' => '5'],
                ['label' => __('None'), 'value' => '6'],
                ['label' => __('Clear Acrylic .020'), 'value' => '7'],
                ['label' => __('UV Protective Acrylic .098'), 'value' => '8'],
                ['label' => __('Metal Slide In - Clear'), 'value' => '9'],
                ['label' => __('Metal Slide In - Non Glare'), 'value' => '10'],
                ['label' => __('Break Resistant Acrylic .060'), 'value' => '11']
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
            'Non Glare .015 PETG',
            'Premium Clear Acrylic',
            'Break Resistant Acrylic .093',
            'Non Glare Acrylic .060',
            'Tempered Glass',
            'Clear Acrylic .040',
            'None',
            'Clear Acrylic .020',
            'UV Protective Acrylic .098',
            'Metal Slide In - Clear',
            'Metal Slide In - Non Glare',
            'Break Resistant Acrylic .060'
        ];
    }
}
