<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class FeaturedSelections extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => 'Most Popular', 'value' => '0'],
                ['label' => 'On Sale', 'value' => '1'],
                ['label' => 'Clearance', 'value' => '2']
            ];
        }
        return $this->_options;
    }
}
