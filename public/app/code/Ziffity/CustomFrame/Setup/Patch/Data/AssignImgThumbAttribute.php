<?php

namespace Ziffity\CustomFrame\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use \Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use \Magento\Catalog\Model\Config;
use \Magento\Eav\Api\AttributeManagementInterface;

class AssignImgThumbAttribute implements DataPatchInterface
{

    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var EavSetupFactory */
    private $eavSetupFactory;

    protected $attributeRepository;

    protected $config;

    protected $attributeManagement;
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        ProductAttributeRepositoryInterface $attributeRepository,
        Config $config,
        AttributeManagementInterface $attributeManagement
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeRepository = $attributeRepository;
        $this->config = $config;
        $this->attributeManagement = $attributeManagement;
    }

    /**
     * @return void
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
        foreach ($attributeSetIds as $attributeSetId) {
            if ($attributeSetId) {
                $group_id = $this->config->getAttributeGroupId($attributeSetId, "Images");
                $this->attributeManagement->assign(
                    'catalog_product',
                    $attributeSetId,
                    $group_id,
                    'img_thumb',
                    999
                );
            }
        }
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [
            InstallM1AttributeSets::class,
            InstallM1Attributes::class,
            CustomFrameAttributes::class,
            InstallAttributeValuesInConfig::class
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
