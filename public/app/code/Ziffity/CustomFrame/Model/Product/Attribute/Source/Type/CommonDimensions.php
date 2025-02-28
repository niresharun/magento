<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

use Ziffity\CustomFrame\Helper\Data;

class CommonDimensions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @var Data
     */
    public $helper;

    /**
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * This function gets the fractional values and converts to float values.
     *
     * @return array|null
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $valuesList = $this->helper->getConfigValue('custom_frame/attribute_values/fractional');
            $valuesList = json_decode($valuesList,true);
            foreach ($valuesList as $elem) {
                if ($elem['value'] <= 3) {
                    continue;
                }
                $this->_options[] = [
                    'value' => $elem['value'],
                    'label' => (string)$this->helper->formatFloatToFractional($elem['value']),
                ];
            }
        }
        return $this->_options;
    }
}
