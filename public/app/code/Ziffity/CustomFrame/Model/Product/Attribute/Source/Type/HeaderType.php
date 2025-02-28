<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

use Magento\Framework\Data\OptionSourceInterface;

class HeaderType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['label' => 'Top', 'value' => 'top'],
                ['label' => 'Bottom', 'value' => 'bottom']
            ];
        }
        return $this->_options;
    }
}
