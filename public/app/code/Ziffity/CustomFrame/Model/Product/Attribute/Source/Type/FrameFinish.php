<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class FrameFinish extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @return array|null
     */
    public function getAllOptions()
    {
        $materials = ['Antiqued','Barnwood','Brushed',
            'Distressed','Gloss','Metallic','Satin','Wood Faux','Wood Grain'];
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
