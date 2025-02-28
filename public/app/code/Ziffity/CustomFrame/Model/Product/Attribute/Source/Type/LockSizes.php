<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class LockSizes extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => 'Yes', 'value' => 1],
                ['label' => 'No', 'value' => 0],
            ];
        }
        return $this->_options;
    }
}
