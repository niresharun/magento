<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class FrameRabbetDepth extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @return array|null
     */
    public function getAllOptions()
    {
        $materials = ['Shallow','Medium','Deep','Extra Deep'];
        if (!$this->_options && $this->_options === null) {
            foreach ($materials as $key=>$material) {
                $this->_options[] = [
                    'value' => $key,
                    'label' => $material,
                ];
            }
        }
        return $this->_options;
    }
}
