<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ziffity\CustomFrame\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Psr\Log\LoggerInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class ChangeAttributeBackendType implements DataPatchInterface
{

    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /** @var EavSetupFactory */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ResourceConnection $resource
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeRepository $attributeRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ResourceConnection $resource,
        EavSetupFactory $eavSetupFactory,
        AttributeRepository $attributeRepository,
        LoggerInterface $logger
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->resource = $resource;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $attributeCodes = [
            'back_of_moulding_width',
            'layer_height',
            'layer_width',
            'mat_height',
            'mat_width',
            'width_of_moulding_for_shell'
        ];
        foreach ($attributeCodes as $attributeCode) {
            $attributeId = $this->findAttributeId($attributeCode);
            if ($attributeId){
                $this->migrateAttributeData($attributeId);
                $this->deleteOldTableData($attributeId);
                $this->updateAttributeBackend($attributeId,'backend_type','decimal');
            }
        }
    }

    /**
     * This function updates the attribute for eav_attribute table.
     *
     * @param string|int $attributeId
     * @param string $field
     * @param string $value
     * @return void
     */
    public function updateAttributeBackend($attributeId, $field, $value)
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

    /**
     * This function finds the attribute_id using the attribute_code.
     *
     * @param string $attributeCode
     * @return false|int|null
     */
    public function findAttributeId($attributeCode)
    {
        try {
            $attribute = $this->attributeRepository->get(Product::ENTITY, $attributeCode);
            return $attribute->getAttributeId();
        }catch (\Magento\Framework\Exception\NoSuchEntityException $exception){
            $this->logger->error($exception->getMessage());
            return false;
        }
    }

    /**
     *This function migrates the data from text to decimal table using attribute_id.
     *
     * @param string|int|null $attributeId
     * @return void
     */
    public function migrateAttributeData($attributeId)
    {
        // Attribute Id that we need to change
        $connection = $this->resource->getConnection();
        // Get the product values that are already stored in the database for given attribute
        $textTable = $this->resource->getTableName('catalog_product_entity_text');
        $attributeValues = $connection->fetchAll("SELECT attribute_id, value , store_id,row_id FROM $textTable where attribute_id = $attributeId");
        if(!empty($attributeValues)) {
            $eavAttributeOption = $this->resource->getTableName('catalog_product_entity_decimal');
            foreach ($attributeValues as $attributeValue) {
                try {
                    $connection->insertMultiple($eavAttributeOption, [$attributeValue]);
                } catch (\Exception $exception) {
                    $this->logger->error($exception->getMessage());
                }
            }
        }
    }

    /**
     * This function deletes the entries from the table using attribute_id.
     *
     * @param string|int $attributeId
     * @return void
     */
    public function deleteOldTableData($attributeId)
    {
        try {
            $connection = $this->resource->getConnection();
            $textTable = $this->resource->getTableName('catalog_product_entity_text');
            $connection->delete($textTable,'attribute_id='.$attributeId);
        }catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
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
        return [RepairTemplateAttributes::class];
    }
}
