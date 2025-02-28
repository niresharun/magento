<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Source\Type;

class NielsenProfiles extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @return array|null
     */
    public function getAllOptions()
    {
        $profiles = ['Profile 15','Profile 11','Profile 12','Profile 21',
            'Profile 22','Profile 24','Profile 26','Profile 33','Profile 34',
            'Profile 35','Profile 37','Profile 45','Profile 58','Profile 65',
            'Profile 75','Profile 94','Profile 95','Profile 97','Profile 99',
            'Profile 117','Profile 126','Profile 127','Profile 225'];
        if (!$this->_options && $this->_options === null) {
            foreach ($profiles as $key=>$profile) {
                $this->_options[] = [
                    'value' => $key,
                    'label' => $profile,
                ];
            }
        }
        return $this->_options;
    }
}
