<?php
namespace Ziffity\Coproduct\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class ApplicableTo extends AbstractSource
{
    public function getAllOptions()
    {
        $options = [
            ['label' => 'Custom Frame', 'value' => 'Custom Frame'],
            ['label' => 'Glass', 'value' => 'Glass'],
            ['label' => 'Backing Board', 'value' => 'Backing Board'],
        ];
        return $options;
    }
}
