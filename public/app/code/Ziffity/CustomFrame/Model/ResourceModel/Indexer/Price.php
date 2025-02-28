<?php

declare(strict_types=1);
namespace Ziffity\CustomFrame\Model\ResourceModel\Indexer;

use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\AbstractIndexer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\BaseFinalPrice;
use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructureFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BasePriceModifier;
use mysql_xdevapi\Exception;
use Ziffity\ProductCustomizer\Model\CompositeConfigProvider;
use Ziffity\CustomFrame\Model\Product\Price as CustomFramePrice;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Psr\Log\LoggerInterface;

/**
 * CustomFrame Product Type Price Indexer
 */
class Price implements DimensionalIndexerInterface
{
    /**
     * @var BaseFinalPrice
     */
    private $baseFinalPrice;

    /**
     * @var IndexTableStructureFactory
     */
    private $indexTableStructureFactory;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @var string
     */
    private $productType;

    /**
     * @var BasePriceModifier
     */
    private $basePriceModifier;

    protected $compositeConfigProvider;

    protected $customframePrice;

    protected $productRepository;

    protected $logger;

    /**
     * @param BaseFinalPrice $baseFinalPrice
     * @param IndexTableStructureFactory $indexTableStructureFactory
     * @param TableMaintainer $tableMaintainer
     * @param BasePriceModifier $basePriceModifier
     * @param string $productType
     */
    public function __construct(
        BaseFinalPrice $baseFinalPrice,
        IndexTableStructureFactory $indexTableStructureFactory,
        TableMaintainer $tableMaintainer,
        BasePriceModifier $basePriceModifier,
        CompositeConfigProvider $compositeConfigProvider,
        CustomFramePrice $customframePrice,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        $productType = \Ziffity\CustomFrame\Model\Product\Type::TYPE_CODE
    ) {
        $this->baseFinalPrice = $baseFinalPrice;
        $this->indexTableStructureFactory = $indexTableStructureFactory;
        $this->tableMaintainer = $tableMaintainer;
        $this->productType = $productType;
        $this->basePriceModifier = $basePriceModifier;
        $this->compositeConfigProvider = $compositeConfigProvider;
        $this->customframePrice = $customframePrice;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function executeByDimensions(array $dimensions, \Traversable $entityIds)
    {
        $this->tableMaintainer->createMainTmpTable($dimensions);

        $temporaryPriceTable = $this->indexTableStructureFactory->create([
            'tableName' => $this->tableMaintainer->getMainTmpTable($dimensions),
            'entityField' => 'entity_id',
            'customerGroupField' => 'customer_group_id',
            'websiteField' => 'website_id',
            'taxClassField' => 'tax_class_id',
            'originalPriceField' => 'price',
            'finalPriceField' => 'final_price',
            'minPriceField' => 'min_price',
            'maxPriceField' => 'max_price',
            'tierPriceField' => 'tier_price',
        ]);
        $select = $this->baseFinalPrice->getQuery($dimensions, $this->productType, iterator_to_array($entityIds));
        $this->tableMaintainer->insertFromSelect(
            $select,
            $temporaryPriceTable->getTableName(),
            [
                "entity_id",
                "customer_group_id",
                "website_id",
                "tax_class_id",
                "price",
                "final_price",
                "min_price",
                "max_price",
                "tier_price",
            ]
        );

        try {
//            $entityIds = ['3129'];
            $select = $this->tableMaintainer->getConnection()->select()->from($temporaryPriceTable->getTableName(), "*");
            $select->where('entity_id IN (?)', $entityIds->toArray());
//            $select->where('entity_id IN (?)', $entityIds);
            $rows = $this->tableMaintainer->getConnection()->query($select)->fetchAll();
            $cacheStorage = [];
            $price = 0;
            foreach ($rows as $row){
                if(array_key_exists($row['entity_id'], $cacheStorage)){
                    $price = $cacheStorage[$row['entity_id']];
                    continue;
                }else{
                    $product = $this->productRepository->getById($row['entity_id']);
                    $defaultData = $this->compositeConfigProvider->getDefaultConfig($product);
                    $price = $this->customframePrice->getPrice($product, $defaultData['options']);
                    $cacheStorage[$row['entity_id']] = $price;
                }
                $data = [
                    "price" => $price,
                    "final_price" => $price,
                    "min_price" => $price,
                    "max_price" => $price
                ];
                $this->tableMaintainer->getConnection()->update($temporaryPriceTable->getTableName(),
                    $data,
                    ['entity_id = ?' => (int)$row['entity_id']]
                );
            }
        }
        catch (\Exception $e){
            $this->logger->critical($e);
        }
        $this->basePriceModifier->modifyPrice($temporaryPriceTable, iterator_to_array($entityIds));
    }
}
