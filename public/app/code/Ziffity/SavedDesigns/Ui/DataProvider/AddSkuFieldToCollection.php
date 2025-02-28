<?php
namespace Ziffity\SavedDesigns\Ui\DataProvider;
class AddSkuFieldToCollection implements \Magento\Ui\DataProvider\AddFieldToCollectionInterface
{
    public function addField(\Magento\Framework\Data\Collection $collection, $field, $alias = null)
    {
        $joinTable = $collection->getTable('catalog_product_entity');
        $collection->getSelect()->joinLeft(
            ['catalog_product_entity' => $joinTable],
            'main_table.product_id = catalog_product_entity.entity_id',
            ['sku']
        );
    }
}
