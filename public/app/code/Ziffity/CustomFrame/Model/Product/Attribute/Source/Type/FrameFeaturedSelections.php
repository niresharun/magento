<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class FrameFeaturedSelections extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @return array|null
     */
    public function getAllOptions()
    {
        if (!$this->_options && $this->_options === null) {
            $this->_options[] = [
                'value' => '1',
                'label' => 'Most Popular',
            ];
            $this->_options[] = [
                'value' => '2',
                'label' => 'On Sale',
            ];
            $this->_options[] = [
                'value' => '3',
                'label' => 'Clearance',
            ];
        }
        return $this->_options;
    }
}
