<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ziffity\CustomFrame\Setup\Patch\Data;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as EavCollection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Product;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class RenameAttributeCodes implements DataPatchInterface
{

    protected $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var EavCollection
     */
    public $eavCollection;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        EavCollection $eavCollection
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavCollection = $eavCollection;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $attributeId = $this->getIdUsingCode('dimension_1');
        if ($attributeId) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $attributeId,
                'frontend_label',
                'Available Width'
            );
        }
        $attributeId = $this->getIdUsingCode('dimension_2');
        if ($attributeId) {
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $attributeId,
                'frontend_label',
                'Available Height'
            );
        }
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
        return [
            InstallM1AttributeSets::class,
            InstallM1Attributes::class
        ];
    }

    public function getIdUsingCode($attributeCode)
    {
        $collection = $this->eavCollection->create();
        $collection->addFieldToFilter('attribute_code',$attributeCode)
            ->getFirstItem();
        if (!empty($collection->getItems())) {
            return $collection->getFirstItem()->getAttributeId();
        }
        return false;
    }
}
