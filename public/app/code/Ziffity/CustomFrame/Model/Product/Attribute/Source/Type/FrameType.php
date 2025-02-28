<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

use Magento\Framework\Data\OptionSourceInterface;

class FrameType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label'=>'-- Please select --', 'value'=>''],
                ['label' => 'Traditional Frame', 'value' => 'traditional_frame'],
                ['label' => 'Slide-In Frame', 'value' => 'slide_in_frame'],
                ['label' => 'Snap Frame', 'value' => 'snap_frame'],
                ['label' => 'Swing Frame', 'value' => 'swing_frame']
            ];
        }
        return $this->_options;
    }
}
