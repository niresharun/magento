<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class PatternFabric extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Default values for option cache
     *
     * @var array
     */
    protected $_optionsDefault = array(
        'birch' => 'Birch',
        'subtle-weave' => 'Subtle Weave',
    );

    /**
     * Retrieve Full Option values array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            foreach ($this->_optionsDefault as $value => $label) {
                $this->_options[] = [
                    'value' => $value,
                    'label' => __($label),
                ];
            }
        }

        return $this->_options;
    }
}
