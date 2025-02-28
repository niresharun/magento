<?php
namespace Ziffity\SavedDesigns\Ui\DataProvider;
class AddSkuFilterToCollection implements \Magento\Ui\DataProvider\AddFilterToCollectionInterface
{
    public function addFilter(\Magento\Framework\Data\Collection $collection, $field, $condition = null)
    {
        if (isset($condition['like'])) {
            $collection->addFieldToFilter($field, $condition);
        }
    }
}
