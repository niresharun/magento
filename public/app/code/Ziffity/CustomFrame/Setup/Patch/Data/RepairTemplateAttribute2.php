<?php

declare(strict_types=1);

namespace Ziffity\CustomFrame\Setup\Patch\Data;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Psr\Log\LoggerInterface;
use Magento\Eav\Model\Entity\Type;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class RepairTemplateAttribute2 implements DataPatchInterface
{

    /**
     * @var Type
     */
    protected $type;
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
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param LoggerInterface $logger
     * @param Type $type
     * @param AttributeRepository $attributeRepository
     */
    public function __construct
    (
        ModuleDataSetupInterface $moduleDataSetup,
        LoggerInterface          $logger,
        Type                     $type,
        AttributeRepository      $attributeRepository
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
        $this->type = $type;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            RepairTemplateAttributes::class
        ];
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        try {
            $entityTypeId = $this->type->loadByCode(Product::ENTITY)->getId();
            $attributeCode = 'country_of_manufacture';
            $attributeId = $this->checkIfAttributeInstalled($attributeCode);
            if ($attributeId) {
                $catalogUpdateData = [
                    'is_filterable' => 0,
                    'is_searchable' => 0,
                    'is_comparable' => 0,
                    'is_visible_on_front' => 0,
                    'is_html_allowed_on_front' => 0,
                    'is_used_for_price_rules' => 0,
                    'is_filterable_in_search' => 0,
                    'used_in_product_listing' => 0,
                    'used_for_sort_by' => 0,
                ];
                //For updating the catalog_eav_attribute table
                $this->moduleDataSetup->updateTableRow(
                    'catalog_eav_attribute',
                    'attribute_id',
                    $attributeId,
                    $catalogUpdateData,
                    null,
                    'entity_type_id',
                    $entityTypeId
                );
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * This function checks if the attribute is installed in M2 or not.
     *
     * @param string $attributeCode
     * @return bool|string|int
     */
    public function checkIfAttributeInstalled($attributeCode)
    {
        try {
            $entityTypeId = $this->type->loadByCode(Product::ENTITY)->getId();
            $attribute = $this->attributeRepository->get($entityTypeId, $attributeCode);
            return $attribute->getAttributeId();
        } catch (NoSuchEntityException $exception) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
