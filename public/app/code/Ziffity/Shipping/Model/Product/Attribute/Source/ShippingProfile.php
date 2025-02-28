<?php

namespace Ziffity\Shipping\Model\Product\Attribute\Source;

use Ziffity\Shipping\Model\ShippingProfile\ResourceModel\ShippingProfile\Collection;
use Ziffity\Shipping\Model\ShippingProfile\ResourceModel\ShippingProfile\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class ShippingProfile extends AbstractSource
{

    /**
     * @var Collection
     */
    public $collection;

    /**
     * @param CollectionFactory $collection
     */
    public function __construct(CollectionFactory $collection)
    {
        $this->collection = $collection;
    }

    /**
     * This function gets all the profiles from the collections.
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options=[['label'=>'-- Please select --', 'value'=>'']];
        $collection = $this->collection->create();
        foreach ($collection->getItems() as $item) {
            $this->_options[] = ['value' => (int)$item->getProfileId(),
                'label' => __($item->getProfileName())];
        }
        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|int $value
     * @return string|false
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}
