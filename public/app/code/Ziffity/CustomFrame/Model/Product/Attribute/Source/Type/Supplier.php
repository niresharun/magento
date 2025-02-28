<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class Supplier extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => __(' '), 'value' => ''],
                ['label' => __('Alpina'), 'value' => '0'],
                ['label' => __('Bangor Cork'), 'value' => '1'],
                ['label' => __('Chemetal'), 'value' => '2'],
                ['label' => __('Crescent / Berkshire'), 'value' => '3'],
                ['label' => __('Decor'), 'value' => '4'],
                ['label' => __('Designer'), 'value' => '5'],
                ['label' => __('Don Mar'), 'value' => '6'],
                ['label' => __('Framerica'), 'value' => '7'],
                ['label' => __('LaminArt'), 'value' => '8'],
                ['label' => __('Nevamar'), 'value' => '9'],
                ['label' => __('Nielsen Bainbridge'), 'value' => '10'],
                ['label' => __('Omega'), 'value' => '11'],
                ['label' => __('Peterboro'), 'value' => '12'],
                ['label' => __('Profiles Frameware'), 'value' => '13'],
                ['label' => __('Studio'), 'value' => '14'],
                ['label' => __('SwingFame'), 'value' => '15'],
                ['label' => __('Tensator'), 'value' => '16'],
                ['label' => __('True Textiles / Guilford'), 'value' => '17'],
                ['label' => __('United Visual'), 'value' => '18'],
                ['label' => __('WilsonArt'), 'value' => '19'],
                ['label' => __('WL Concepts'), 'value' => '20'],
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
            'Alpina',
            'Bangor Cork',
            'Chemetal',
            'Crescent / Berkshire',
            'Decor',
            'Designer',
            'Don Mar',
            'Framerica',
            'LaminArt',
            'Nevamar',
            'Nielsen Bainbridge',
            'Omega',
            'Peterboro',
            'Profiles Frameware',
            'Studio',
            'SwingFame',
            'Tensator',
            'True Textiles / Guilford',
            'United Visual',
            'WilsonArt',
            'WL Concepts'
        ];
    }
}
