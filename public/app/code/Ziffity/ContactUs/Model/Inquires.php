<?php

namespace Ziffity\ContactUs\Model;

class Inquires extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{


    /**
     * @return array|array[]|null
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('General Information'), 'value' => 'general_information'],
                ['label' => __('Get a Quote '), 'value' => 'get_quote'],
                ['label' => __('Order Status'), 'value' => 'order_status']
            ];
        }
        return $this->_options;
    }
}
