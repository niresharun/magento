<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

use Ziffity\CustomFrame\Helper\Data;

class MatboardOverlap extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * This function gets the jsonFractionalValues and converts to float values.
     *
     * @return array|null
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $valuesList = $this->helper->getConfigValue('custom_frame/attribute_values/fractional');
            $valuesList = json_decode($valuesList,true);
            foreach ($valuesList as $elem) {
                $this->_options[] = [
                    'value' => number_format($elem['value'],6),
                    'label' => __($this->helper->formatFloatToFractional($elem['value'])),
                ];
            }
        }
        return $this->_options;
    }
}
