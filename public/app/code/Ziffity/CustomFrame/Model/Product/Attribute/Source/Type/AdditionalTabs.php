<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class AdditionalTabs extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => __('Headers'), 'value' => 'Headers'],
                ['label' => __('Labels'), 'value' => 'Labels'],
                ['label' => __('Openings'), 'value' => 'Openings'],
                ['label' => __('Shelves'), 'value' => 'Shelves'],
                ['label' => __('Addons'), 'value' => 'Addons'],
                ['label' => __('Lighting'), 'value' => 'Lighting']
            ];
        }
        return $this->_options;
    }
}
