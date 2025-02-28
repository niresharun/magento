<?php

namespace Ziffity\Coproduct\Model\Rule\Condition;

/**
 * Catalog Rule Product Condition data model
 */
class AssociatedProduct extends \Magento\CatalogRule\Model\Rule\Condition\Product
{

    /**
     * Get attribute name
     *
     * @return string
     */
    public function getAttribute()
    {
        $value = explode('>', $this->getData('attribute'));
        return isset($value[1]) ? $value[1] : $this->getData('attribute');
    }

    /**
     * Get attribute set
     *
     * @return string
     */
    public function getAttributeSet()
    {
        $value = explode('>', $this->getData('attribute'));
        return isset($value[1]) ? $value[0] : null;
    }

    /**
     * Validate product attribute value for condition
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return boolean
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getNonCustomframe() == 1) {
            return parent::validate($object);
        }

        if (($set = $this->getAttributeSet()) && $object->getData('customizer_products/' . $set)) {
            if (is_array($object->getData('customizer_products/' . $set))) {
                foreach ($object->getData('customizer_products/' . $set) as $item) {
                    if (parent::validate($item)) {
                        return true;
                    }
                }
                return false;
            }
            $object = $object->getData('customizer_products/' . $set);
        }

        return parent::validate($object);
    }

    /**
     * Retrieve attribute element
     *
     * @return string
     */
    public function getAttributeElementHtml()
    {
        return ($this->getAttributeSet() ? ucwords(str_replace('_', ' ', $this->getAttributeSet()))  . ': ' : '') . parent::getAttributeElementHtml();
    }

    public function getAttributeElement()
    {
        if (null === $this->getAttribute()) {
            $options = $this->getAttributeOption();
            if ($options) {
                reset($options);
                $this->setAttribute(key($options));
            }
        }
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__attribute',
            'select',
            [
                'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][attribute]',
                'values' => $this->getAttributeSelectOptions(),
                'value' => $this->getData('attribute'),
                'value_name' => $this->getAttributeName(),
                'data-form-part' => $this->getFormName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class)
        )->setShowAsText(true);
    }

    /**
     * Retrieve attribute element
     *
     * @return []
     */
    public function asArray(array $arrAttributes = array())
    {
        $out = array(
            'type'               => $this->getType(),
            'attribute'          => $this->getData('attribute'),
            'operator'           => $this->getOperator(),
            'value'              => $this->getValue(),
            'is_value_processed' => $this->getIsValueParsed(),
        );
        return $out;
    }
}
