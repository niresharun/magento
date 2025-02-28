<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

use Magento\Framework\Data\OptionSourceInterface;

class DepthType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => __('None'), 'value' => 'none'],
                ['label' => __('Insert Thickness'), 'value' => 'graphic_thickness'],
                ['label' => __('Interior Depth'), 'value' => 'interior_depth']
            ];
        }
        return $this->_options;
    }
}
