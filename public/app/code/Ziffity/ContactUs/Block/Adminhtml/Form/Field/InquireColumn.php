<?php

namespace Ziffity\ContactUs\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;


class InquireColumn extends Select
{
    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @param $value
     * @return InquireColumn
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * @return array[]
     */
    private function getSourceOptions()
    {
        return [
            ['value' => 'general_information', 'label' => __('General Information')],
            ['value' => 'get_quote', 'label' => __('Get a Quote')],
            ['value' => 'order_status', 'label' => __('Order Status')]
        ];
    }
}
