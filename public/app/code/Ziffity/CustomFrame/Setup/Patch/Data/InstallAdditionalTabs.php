<?php

namespace Ziffity\CustomFrame\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Ziffity\CustomFrame\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use \Magento\Catalog\Api\ProductAttributeRepositoryInterface;

class InstallAdditionalTabs implements DataPatchInterface
{

    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var EavSetupFactory */
    private $eavSetupFactory;

    protected $attributeRepository;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        ProductAttributeRepositoryInterface $attributeRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return void
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute('catalog_product', 'additional_tabs', [
            'type' => 'text',
            'label' => 'Additional Tabs',
            'input' => 'multiselect',
            'class' => '',
            'source' => 'Ziffity\CustomFrame\Model\Product\Attribute\Source\Type\AdditionalTabs',
            'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'default' => '0',
            'searchable' => true,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'unique' => false
        ]);
        $attribute = $this->attributeRepository->get('additional_tabs');
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $matAttributeSetId = $eavSetup->getAttributeSetId($entityTypeId, 'Custom Frame');
        $groupId = $eavSetup->getAttributeGroupId($entityTypeId, $matAttributeSetId, 'General');
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'additional_tabs',
            'apply_to',
            'customframe'
        );
        $eavSetup->addAttributeToGroup(
            $entityTypeId,
            $matAttributeSetId,
            $groupId,
            $attribute->getId(),
            null
        );
    }

    /**
    * @return array
    */
    public static function getDependencies()
    {
        return [
            InstallM1AttributeSets::class,
            InstallM1Attributes::class
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
