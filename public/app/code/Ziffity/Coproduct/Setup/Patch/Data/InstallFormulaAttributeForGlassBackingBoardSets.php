<?php

namespace Ziffity\Coproduct\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Ziffity\CustomFrame\Setup\Patch\Data\InstallM1Attributes;
use Ziffity\CustomFrame\Setup\Patch\Data\InstallM1AttributeSets;

class InstallFormulaAttributeForGlassBackingBoardSets implements DataPatchInterface
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
        EavSetupFactory          $eavSetupFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public static function getDependencies()
    {
        return [InstallM1AttributeSets::class];
    }

    public function apply()
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute('catalog_product', 'formula', [
            'type' => 'text',
            'label' => 'Formula',
            'input' => 'textarea',
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'backend' => \Ziffity\Coproduct\Model\Product\Attribute\Backend\LookupFunctionValidate::class,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => false,
            'unique' => false,
            'apply_to' => 'customframe'
        ]);
        $attributeCodes = ['formula'];
        foreach ($attributeCodes as $attributeCode) {
            $this->assignToAllGlassBackingBoardAttributeSets($attributeCode);
        }
    }

    public function assignToAllGlassBackingBoardAttributeSets($attributeCode)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attributeSetNames = ['Glass', 'Backing Board'];
        foreach ($attributeSetNames as $attributeSetName) {
            $specificAttributeSetId = $eavSetup->getAttributeSetId(\Magento\Catalog\Model\Product::ENTITY, $attributeSetName);
            $this->assignAttributeSetGroup($attributeCode, $specificAttributeSetId);
        }
    }

    /**
     * This function assigns the attribute sets and groups to the attribute.
     *
     * @param array $data
     * @param string $attributeSetId
     * @param string $attributeGroupName
     * @return void
     */
    public function assignAttributeSetGroup($attributeCode, $attributeSetId)
    {
        //your custom attribute group/tab
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attrGroupId = $eavSetup->getAttributeGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'General'
        );
        if (!$attrGroupId) {
            $eavSetup->addAttributeGroup(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeSetId,
                'General' // attribute group name
            );
        }
        //add attribute to group
        $eavSetup->addAttributeToGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'General', // attribute group
            $attributeCode // attribute code
        );
    }

    public function getAliases()
    {
        return [];
    }
}
