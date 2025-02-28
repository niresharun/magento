<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * @api
 * @since 100.0.2
 */
class TemplateWithShelves extends AbstractSource
{
    /**
     * Option values
     */
    const VALUE_YES = 1;

    const VALUE_NO = 0;

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => 'Yes', 'value' => self::VALUE_YES],
                ['label' => 'No', 'value' => self::VALUE_NO],
            ];
        }
        return $this->_options;
    }
}
