<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ziffity\CustomFrame\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as EavCollection;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class ChangeAttributeSourceModel implements DataPatchInterface
{

    /**
     * @var EavCollection
     */
    public $eavCollection;

    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /** @var EavSetupFactory */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavCollection $eavCollection
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup,
                                EavCollection $eavCollection,
                                EavSetupFactory $eavSetupFactory)
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavCollection = $eavCollection;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $oldSourceModel = 'Ziffity\CustomFrame\Model\Product\Attribute\Source\Type\MatboardOverlap';
        $newSourceModel = 'Ziffity\CustomFrame\Model\Product\Attribute\Source\Type\Fractional';
        $attributeId = $this->findAttributeId($oldSourceModel);
        foreach ($attributeId as $value) {
            $this->updateAttributeBackend($value->getAttributeId(), 'source_model', $newSourceModel);
        }
    }

    public function updateAttributeBackend($attributeId,$field,$value)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeId,
            $field,
            $value
        );
    }

    public function findAttributeId($sourceModel)
    {
        $collection = $this->eavCollection->create();
        $collection->addFieldToFilter('source_model',$sourceModel);
        if (!empty($collection->getData())) {
            return $collection->getItems();
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [RepairTemplateAttributes::class];
    }
}
