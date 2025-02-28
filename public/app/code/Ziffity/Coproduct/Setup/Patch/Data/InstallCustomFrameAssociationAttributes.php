<?php

namespace Ziffity\Coproduct\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InstallCustomFrameAssociationAttributes implements DataPatchInterface
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
        return [];
    }

    public function apply()
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        //quantity_required attribute installation
        $eavSetup->addAttribute('catalog_product', 'quantity_required', [
            'type' => 'int',
            'label' => 'Quantity Required',
            'input' => 'text',
            'class' => '',
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => 'coproduct'
        ]);
        //customframe_size attribute installation
        $eavSetup->addAttribute('catalog_product', 'customframe_size', [
            'type' => 'text',
            'label' => 'Size',
            'input' => 'textarea',
            'class' => '',
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'backend' => \Ziffity\Coproduct\Model\Product\Attribute\Backend\LookupFunctionValidate::class,
            'frontend'=> \Ziffity\Coproduct\Model\Product\Attribute\Frontend\GetSizeModel::class,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => 'coproduct'
        ]);
        //customframe_value attribute installation
        $eavSetup->addAttribute('catalog_product', 'customframe_value', [
            'type' => 'text',
            'label' => 'Value',
            'input' => 'textarea',
            'class' => '',
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'backend' => \Ziffity\Coproduct\Model\Product\Attribute\Backend\LookupFunctionValidate::class,
            'frontend'=> \Ziffity\Coproduct\Model\Product\Attribute\Frontend\GetSizeModel::class,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => 'coproduct'
        ]);
        //customframe_price attribute installation
        $eavSetup->addAttribute('catalog_product', 'customframe_price', [
            'type' => 'text',
            'label' => 'Price',
            'input' => 'textarea',
            'class' => '',
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'backend'=>\Ziffity\Coproduct\Model\Product\Attribute\Backend\LookupFunctionValidate::class,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => 'coproduct'
        ]);
        $attributeCodes = ['quantity_required', 'customframe_size',
            'customframe_price','customframe_value'];
        foreach ($attributeCodes as $attributeCode) {
            $this->assignToAllAttributeSets($attributeCode);
        }
    }

    public function assignToAllAttributeSets($attributeCode)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
        foreach ($attributeSetIds as $attributeSetId) {
            $this->assignAttributeSetGroup($attributeCode, $attributeSetId);
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
            'Custom Frame Association'
        );
        if (!$attrGroupId) {
            $eavSetup->addAttributeGroup(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeSetId,
                'Custom Frame Association' // attribute group name
            );
        }
        //add attribute to group
        $eavSetup->addAttributeToGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'Custom Frame Association', // attribute group
            $attributeCode // attribute code
        );
    }

    public function getAliases()
    {
        return [];
    }
}
