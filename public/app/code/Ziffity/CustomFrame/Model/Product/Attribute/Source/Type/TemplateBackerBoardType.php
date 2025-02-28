<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class TemplateBackerBoardType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => 'Chalk Board Backer', 'value' => '0'],
                ['label' => 'Cork Board Backer', 'value' => '1'],
                ['label' => 'Dry / Wet Erase Backer', 'value' => '2'],
                ['label' => 'Letter Board Backer', 'value' => '3'],
                ['label' => 'Magnetic Backer', 'value' => '4'],
                ['label' => 'Wood Backer', 'value' => '5']
            ];
        }
        return $this->_options;
    }
}
