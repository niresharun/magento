<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

use Ziffity\CustomFrame\Helper\Data;

class CommonDimensionsOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @var Data
     */
    public $helper;

    /**
     * @var array
     */
    public $options = [];

    /**
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * This function gets the jsonFractionalValues and converts to float format.
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options && $this->_options === null) {
            $valuesList = $this->helper->getConfigValue('custom_frame/attribute_values/fractional');
            $valuesList = json_decode($valuesList,true);
            foreach ($valuesList as $elem) {
                $fractional = $this->helper->formatFloatToFractional($elem['value']);
                $this->_options[] = [
                    'value' => $elem['value'],
                    'label' => $fractional,
                ];
            }
        }
        return $this->_options;
    }

    public function getAllOptionsForAdmin()
    {
        if (!$this->_options && $this->_options === null) {
            $valuesList = $this->helper->getConfigValue('custom_frame/attribute_values/fractional');
            $valuesList = json_decode($valuesList,true);
            foreach ($valuesList as $elem) {
                $fractional = $this->helper->formatFloatToFractional($elem['value']);
                $this->_options[] = [
                    'value' => $elem['value'],
                    'label' => __($fractional),
                ];
            }
        }
        return $this->_options;
    }
}
