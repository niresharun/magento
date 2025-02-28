<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class LaminateFinish extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @const integer Exterior Finish Option.
     */
    const EXTERIOR_FINISH = 1;

    /**
     * @const integer Exterior Finish Option.
     */
    const INTERIOR_FINISH = 2;

    /**
     * Default values for option cache
     *
     * @var array
     */
    protected $_optionsDefault = array(
        '' => '',
        self::EXTERIOR_FINISH => 'Exterior Finish',
        self::INTERIOR_FINISH => 'Interior Finish',
    );

    /**
     * Retrieve all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __(' '), 'value' => null]];
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
