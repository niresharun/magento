<?php

namespace Ziffity\CustomFrame\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateAdditionalTabs implements DataPatchInterface
{

    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var EavSetupFactory */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * This patch updates the attribute - not to show the label,value in frontend.
     *
     * @return void
     */
    public function apply()
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'additional_tabs',
            'is_visible_on_front',
            false
        );
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'additional_tabs',
            'backend_model',
            'Ziffity\CustomFrame\Model\Product\Attribute\Backend\SaveMultipleDimensions'
        );
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [
            InstallAdditionalTabs::class
        ];
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
