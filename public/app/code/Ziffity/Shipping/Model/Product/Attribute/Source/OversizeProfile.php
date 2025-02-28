<?php

namespace Ziffity\Shipping\Model\Product\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Ziffity\Shipping\Model\OversizeProfile\ResourceModel\OversizeProfile\Collection;
use Ziffity\Shipping\Model\OversizeProfile\ResourceModel\OversizeProfile\CollectionFactory;

class OversizeProfile extends AbstractSource
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
     * This function gets all the profiles from the collection.
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
