<?php

namespace Ziffity\CustomFrame\Model\Product;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as Selections;
use Magento\Bundle\Model\ResourceModel\Selection\Collection\FilterApplier as SelectionCollectionFilterApplier;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;

class Type extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    /**
     * Product type
     */
    public const TYPE_CODE = 'customframe';

    /**
     * Product is composite
     *
     * @var bool
     */
    protected $_isComposite = true;

    /**
     * Cache key for Options Collection
     *
     * @var string
     */
    protected $_keyOptionsCollection = '_cache_instance_options_collection';

    /**
     * Cache key for Selections Collection
     *
     * @var string
     * @deprecated 100.2.0
     * @see MAGETWO-71174
     */
    protected $_keySelectionsCollection = '_cache_instance_selections_collection';

    /**
     * Cache key for used Selections
     *
     * @var string
     */
    protected $_keyUsedSelections = '_cache_instance_used_selections';

    /**
     * Cache key for used selections ids
     *
     * @var string
     */
    protected $_keyUsedSelectionsIds = '_cache_instance_used_selections_ids';

    /**
     * Cache key for used options
     *
     * @var string
     */
    protected $_keyUsedOptions = '_cache_instance_used_options';

    /**
     * Cache key for used options ids
     *
     * @var string
     */
    protected $_keyUsedOptionsIds = '_cache_instance_used_options_ids';

    /**
     * Product is possible to configure
     *
     * @var bool
     */
    protected $_canConfigure = true;

    /**
     * Catalog data helper
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * Catalog product helper
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProduct = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Bundle\Model\OptionFactory
     */
    protected $_bundleOption;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Selection
     */
    protected $_bundleSelection;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory
     */
    protected $_bundleCollection;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\BundleFactory
     */
    protected $_bundleFactory;

    /**
     * @var \Magento\Bundle\Model\SelectionFactory $bundleModelSelection
     */
    protected $_bundleModelSelection;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $_stockState;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var SelectionCollectionFilterApplier
     */
    private $selectionCollectionFilterApplier;

    /**
     * @var ArrayUtils
     */
    private $arrayUtility;

    /**
     * @param \Magento\Catalog\Model\Product\Option $catalogProductOption
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Bundle\Model\SelectionFactory $bundleModelSelection
     * @param \Magento\Bundle\Model\ResourceModel\BundleFactory $bundleFactory
     * @param \Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory $bundleCollection
     * @param \Magento\Catalog\Model\Config $config
     * @param \Magento\Bundle\Model\ResourceModel\Selection $bundleSelection
     * @param \Magento\Bundle\Model\OptionFactory $bundleOption
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @param Json|null $serializer
     * @param MetadataPool|null $metadataPool
     * @param SelectionCollectionFilterApplier|null $selectionCollectionFilterApplier
     * @param ArrayUtils|null $arrayUtility
     * @param UploaderFactory $uploaderFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Bundle\Model\SelectionFactory $bundleModelSelection,
        \Magento\Bundle\Model\ResourceModel\BundleFactory $bundleFactory,
        \Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory $bundleCollection,
        \Magento\Catalog\Model\Config $config,
        \Magento\Bundle\Model\ResourceModel\Selection $bundleSelection,
        \Magento\Bundle\Model\OptionFactory $bundleOption,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        Json $serializer = null,
        MetadataPool $metadataPool = null,
        SelectionCollectionFilterApplier $selectionCollectionFilterApplier = null,
        ArrayUtils $arrayUtility = null,
        UploaderFactory $uploaderFactory = null
    ) {
        $this->_catalogProduct = $catalogProduct;
        $this->_catalogData = $catalogData;
        $this->_storeManager = $storeManager;
        $this->_bundleOption = $bundleOption;
        $this->_bundleSelection = $bundleSelection;
        $this->_config = $config;
        $this->_bundleCollection = $bundleCollection;
        $this->_bundleFactory = $bundleFactory;
        $this->_bundleModelSelection = $bundleModelSelection;
        $this->priceCurrency = $priceCurrency;
        $this->_stockRegistry = $stockRegistry;
        $this->_stockState = $stockState;

        $this->metadataPool = $metadataPool
            ?: ObjectManager::getInstance()->get(MetadataPool::class);

        $this->selectionCollectionFilterApplier = $selectionCollectionFilterApplier
            ?: ObjectManager::getInstance()->get(SelectionCollectionFilterApplier::class);
        $this->arrayUtility= $arrayUtility ?: ObjectManager::getInstance()->get(ArrayUtils::class);

        parent::__construct(
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository,
            $serializer,
            $uploaderFactory
        );
    }

    /**
     * Return relation info about used products
     *
     * @return \Magento\Framework\DataObject Object with information data
     */
    public function getRelationInfo()
    {
        $info = new \Magento\Framework\DataObject();
        $info->setTable('catalog_product_bundle_selection')
            ->setParentFieldName('parent_product_id')
            ->setChildFieldName('product_id');

        return $info;
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int $parentId
     * @param bool $required
     * @return array
     */
    public function getChildrenIds($parentId, $required = true)
    {
        return $this->_bundleSelection->getChildrenIds($parentId, $required);
    }

    /**
     * Retrieve parent ids array by required child
     *
     * @param int|array $childId
     * @return array
     */
    public function getParentIdsByChild($childId)
    {
        return $this->_bundleSelection->getParentIdsByChild($childId);
    }

    public function getSku($product)
    {
        $sku = $product->getData('sku');
        if ($product->getCustomOption('option_ids')) {
            $sku = $this->getOptionSku($product, $sku);
        }
        return $sku;
    }

    /**
     * Return product weight based on weight_type attribute
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getWeight($product)
    {
        $weight = 0;
        if ($product->getData('weight_type')) {
            $weight = $product->getData('weight');
        }
        return $weight;
    }

    /**
     * Check is virtual product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isVirtual($product)
    {
        return false;
    }

    /**
     * Before save type related data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this|void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function beforeSave($product)
    {
        parent::beforeSave($product);

        // If bundle product has dynamic weight, than delete weight attribute
        if (!$product->getData('weight_type') && $product->hasData('weight')) {
            $product->setData('weight', false);
        }

        if ($product->getPriceType() == Price::PRICE_TYPE_DYNAMIC) {
            /** unset product custom options for dynamic price */
            if ($product->hasData('product_options')) {
                $product->unsetData('product_options');
            }
        }

        $product->canAffectOptions(false);

        if ($product->getCanSaveBundleSelections()) {
            $product->canAffectOptions(true);
            $selections = $product->getBundleSelectionsData();
            if (!empty($selections) && $options = $product->getBundleOptionsData()) {
                foreach ($options as $option) {
                    if (empty($option['delete']) || 1 != (int)$option['delete']) {
                        $product->setTypeHasOptions(true);
                        if (1 == (int)$option['required']) {
                            $product->setTypeHasRequiredOptions(true);
                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     * Retrieve bundle options items
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Framework\DataObject[]
     */
    public function getOptions($product)
    {
        return $this->getOptionsCollection($product)
            ->getItems();
    }

    /**
     * Retrieve bundle options ids
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getOptionsIds($product)
    {
        return $this->getOptionsCollection($product)
            ->getAllIds();
    }

    /**
     * Retrieve bundle option collection
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    public function getOptionsCollection($product, $optionTitle = null)
    {
        if (!$product->hasData($this->_keyOptionsCollection) || $optionTitle) {
            /** @var Collection $optionsCollection */
            $optionsCollection = $this->_bundleOption->create()
                ->getResourceCollection();
            $optionsCollection->setProductIdFilter($product->getEntityId());

            $this->setStoreFilter($product->getStoreId(), $product);
            $optionsCollection->setPositionOrder();
            $storeId = $this->getStoreFilter($product);
            if ($storeId instanceof \Magento\Store\Model\Store) {
                $storeId = $storeId->getId();
            }

            $optionsCollection->joinValues($storeId);
            if ($optionTitle != null) {
                $optionsCollection->addFieldToFilter(
                    ['option_value.title', 'option_value_default.title'],
                    [$optionTitle, $optionTitle],
                );
            }
            $product->setData($this->_keyOptionsCollection, $optionsCollection);
        }
        return $product->getData($this->_keyOptionsCollection);
    }

    /**
     * Retrieve bundle selections collection based on used options
     *
     * @param array $optionIds
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Bundle\Model\ResourceModel\Selection\Collection
     */
    public function getSelectionsCollection($optionIds, $product,
                                            $optionTitle =null, $pagination = null,
                                            $searchQuery = null, $filters = null)
    {
        $storeId = $product->getStoreId();

        /** @var Selections $selectionsCollection */
        $selectionsCollection = $this->_bundleCollection->create();
        $selectionsCollection
            ->addAttributeToSelect($this->_config->getProductAttributes())
            ->addAttributeToSelect('tax_class_id') //used for calculation item taxes in Bundle with Dynamic Price
            ->setFlag('product_children', true)
            ->addAttributeToFilter('status', ['eq' => ProductStatus::STATUS_ENABLED])
            //->setPositionOrder()
            ->addStoreFilter($this->getStoreFilter($product))
            ->setStoreId($storeId)
            ->addFilterByRequiredOptions()
            ->setOptionIdsFilter($optionIds);
        $selectionsCollection->getSelect()->order('selection.is_default desc')
            ->order('selection.position asc');
        $selectionsCollection = $this->processSelections($selectionsCollection, $product, $pagination, $searchQuery, $filters);
        return $selectionsCollection;
    }

    public function processSelections($selectionsCollection, $product, $pagination = null,
                                      $searchQuery = null, $filters = null)
    {
        $metadata = $this->metadataPool->getMetadata(
            \Magento\Catalog\Api\Data\ProductInterface::class
        );
        $storeId = $product->getStoreId();
        if ($filters) {
            $filters = json_decode($filters, true);
            $groupedFilters = [];
            foreach ($filters as $filter) {
                $groupedFilters[$filter['attribute_code']][] = $filter['id'];
            }
            foreach ($groupedFilters as $attributeCode => $filterIds) {
                $selectionsCollection->addAttributeToSelect($attributeCode);
                $selectionsCollection->addAttributeToFilter($attributeCode, $filterIds);
            }
        }

        if ($selectionsCollection->getSize() && $searchQuery == null) {
            $this->_coreRegistry->register($product->getSku(), $selectionsCollection->getSize(), true);
        }

        if ($searchQuery!==null && !empty($searchQuery)){
            $selectionsCollection->addAttributeToFilter('name',['like'=>'%'.$searchQuery.'%']);
            $this->_coreRegistry->register($product->getSku(),$selectionsCollection->getSize(),true);
        }

        if ($pagination!==null &&!empty($pagination)){
            if (isset($pagination['limit']) && isset($pagination['offset'])){
                $selectionsCollection->setPageSize((int)$pagination['limit']);
                $selectionsCollection->setCurPage((int)$pagination['offset']);
            }
        }

        $this->selectionCollectionFilterApplier->apply(
            $selectionsCollection,
            'parent_product_id',
            $product->getData($metadata->getLinkField())
        );

        if (!$this->_catalogData->isPriceGlobal() && $storeId) {
            $websiteId = $this->_storeManager->getStore($storeId)
                ->getWebsiteId();
            $selectionsCollection->joinPrices($websiteId);
        }
        return $selectionsCollection;
    }

    /**
     * Method is needed for specific actions to change given quote options values
     * according current product type logic
     * Example: the catalog inventory validation of decimal qty can change qty to int,
     * so need to change quote item qty option value too.
     *
     * @param  array $options
     * @param  \Magento\Framework\DataObject $option
     * @param  mixed $value
     * @param  \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function updateQtyOption($options, \Magento\Framework\DataObject $option, $value, $product)
    {
        $optionProduct = $option->getProduct($product);
        $optionUpdateFlag = $option->getHasQtyOptionUpdate();
        $optionCollection = $this->getOptionsCollection($product);

        $selections = $this->getSelectionsCollection($optionCollection->getAllIds(), $product);

        foreach ($selections as $selection) {
            if ($selection->getProductId() == $optionProduct->getId()) {
                foreach ($options as $quoteItemOption) {
                    if ($quoteItemOption->getCode() == 'selection_qty_' . $selection->getSelectionId()) {
                        if ($optionUpdateFlag) {
                            $quoteItemOption->setValue((int) $quoteItemOption->getValue());
                        } else {
                            $quoteItemOption->setValue($value);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Prepare Quote Item Quantity
     *
     * @param mixed $qty
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepareQuoteItemQty($qty, $product)
    {
        return (int) $qty;
    }

    /**
     * Checking if we can sale this bundle
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function isSalable($product)
    {
        if (!parent::isSalable($product)) {
            return false;
        }

        if ($product->hasData('all_items_salable')) {
            return $product->getData('all_items_salable');
        }

        $metadata = $this->metadataPool->getMetadata(
            \Magento\Catalog\Api\Data\ProductInterface::class
        );

        $isSalable = false;
        foreach ($this->getOptionsCollection($product) as $option) {
            $hasSalable = false;

            $selectionsCollection = $this->_bundleCollection->create();
            $selectionsCollection->addAttributeToSelect('status');
            $selectionsCollection->addQuantityFilter();
            $selectionsCollection->setFlag('product_children', true);
            $selectionsCollection->addFilterByRequiredOptions();
            $selectionsCollection->setOptionIdsFilter([$option->getId()]);

            $this->selectionCollectionFilterApplier->apply(
                $selectionsCollection,
                'parent_product_id',
                $product->getData($metadata->getLinkField())
            );

            foreach ($selectionsCollection as $selection) {
                if ($selection->isSalable()) {
                    $hasSalable = true;
                    break;
                }
            }

            if ($hasSalable) {
                $isSalable = true;
            }

            if (!$hasSalable && $option->getRequired()) {
                $isSalable = false;
                break;
            }
        }

        $product->setData('all_items_salable', $isSalable);

        return $isSalable;
    }

    /**
     * Prepare product and its configuration to be added to some products list.
     *
     * Perform standard preparation process and then prepare of bundle selections options.
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return \Magento\Framework\Phrase|array|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        // try to add custom options
        try {
            $options = $this->_prepareOptions($buyRequest, $product, $processMode);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $e->getMessage();
        }

        if (is_string($options)) {
            return $options;
        }
        // try to found super product configuration
        $superProductConfig = $buyRequest->getSuperProductConfig();
        if (!empty($superProductConfig['product_id']) && !empty($superProductConfig['product_type'])) {
            $superProductId = (int)$superProductConfig['product_id'];
            if ($superProductId) {
                /** @var \Magento\Catalog\Model\Product $superProduct */
                $superProduct = $this->_coreRegistry->registry('used_super_product_' . $superProductId);
                if (!$superProduct) {
                    $superProduct = $this->productRepository->getById($superProductId);
                    $this->_coreRegistry->register('used_super_product_' . $superProductId, $superProduct);
                }
                $assocProductIds = $superProduct->getTypeInstance()->getAssociatedProductIds($superProduct);
                if (in_array($product->getId(), $assocProductIds)) {
                    $productType = $superProductConfig['product_type'];
                    $product->addCustomOption('product_type', $productType, $superProduct);

                    $buyRequest->setData(
                        'super_product_config',
                        ['product_type' => $productType, 'product_id' => $superProduct->getId()]
                    );
                }
            }
        }


        $product->prepareCustomOptions();
        $buyRequest->unsetData('_processing_params');
        if($buyRequest->getData('options')){
            $options = ($buyRequest->getData('options'));
            $price = floatval($options['additional_data']['subtotal']);
            $buyRequest->unsetData('options');
            $buyRequest->setData('price', $price);
        } else {
            return __('Please specify product option(s).');
        }


        // One-time params only
        $product->addCustomOption('info_buyRequest', $this->serializer->serialize($buyRequest->getData()));
//        if ($options) {
//            $optionIds = array_keys($options);
//            $product->addCustomOption('option_ids', implode(',', $optionIds));
//            foreach ($options as $optionId => $optionValue) {
//                $product->addCustomOption(self::OPTION_PREFIX . $optionId, $optionValue);
//            }
//        }

        // set quantity in cart
        if ($this->_isStrictProcessMode($processMode)) {
            $product->setCartQty($buyRequest->getQty());
        }
        $product->setQty($buyRequest->getQty());

        return [$product];
    }

    /**
     * Cast array values to int
     *
     * @param array $array
     * @return int[]|int[][]
     */
    private function recursiveIntval(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->recursiveIntval($value);
            } elseif (is_numeric($value) && (int)$value != 0) {
                $array[$key] = (int)$value;
            } else {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Retrieve message for specify option(s)
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSpecifyOptionMessage()
    {
        return __('Please specify product option(s).');
    }

    /**
     * Retrieve bundle selections collection based on ids
     *
     * @param array $selectionIds
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Bundle\Model\ResourceModel\Selection\Collection
     */
    public function getSelectionsByIds($selectionIds, $product)
    {
        sort($selectionIds);

        $metadata = $this->metadataPool->getMetadata(
            \Magento\Catalog\Api\Data\ProductInterface::class
        );

        $usedSelections = $product->getData($this->_keyUsedSelections);
        $usedSelectionsIds = $product->getData($this->_keyUsedSelectionsIds);

        if (!$usedSelections || $usedSelectionsIds !== $selectionIds) {
            $storeId = $product->getStoreId();
            /** @var Selections $usedSelections */
            $usedSelections = $this->_bundleCollection->create();
            $usedSelections
                ->addAttributeToSelect('*')
                ->setFlag('product_children', true)
                ->addAttributeToFilter('status', ['eq' => ProductStatus::STATUS_ENABLED])
                ->addStoreFilter($this->getStoreFilter($product))
                ->setStoreId($storeId)
                ->setPositionOrder()
                ->addFilterByRequiredOptions()
                ->setSelectionIdsFilter($selectionIds);

            $this->selectionCollectionFilterApplier->apply(
                $usedSelections,
                'parent_product_id',
                $product->getData($metadata->getLinkField())
            );

            if (!$this->_catalogData->isPriceGlobal() && $storeId) {
                $websiteId = $this->_storeManager->getStore($storeId)
                    ->getWebsiteId();
                $usedSelections->joinPrices($websiteId);
            }
            $product->setData($this->_keyUsedSelections, $usedSelections);
            $product->setData($this->_keyUsedSelectionsIds, $selectionIds);
        }

        return $usedSelections;
    }

    /**
     * Retrieve bundle options collection based on ids
     *
     * @param array $optionIds
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    public function getOptionsByIds($optionIds, $product)
    {
        sort($optionIds);

        $usedOptions = $product->getData($this->_keyUsedOptions);
        $usedOptionsIds = $product->getData($this->_keyUsedOptionsIds);

        if (!$usedOptions
            || $this->serializer->serialize($usedOptionsIds) != $this->serializer->serialize($optionIds)
        ) {
            $usedOptions = $this->_bundleOption
                ->create()
                ->getResourceCollection()
                ->setProductIdFilter($product->getId())
                ->setPositionOrder()
                ->joinValues(
                    $this->_storeManager->getStore()
                        ->getId()
                )
                ->setIdFilter($optionIds);
            $product->setData($this->_keyUsedOptions, $usedOptions);
            $product->setData($this->_keyUsedOptionsIds, $optionIds);
        }

        return $usedOptions;
    }

    /**
     * Prepare additional options/information for order item which will be created from this product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getOrderOptions($product)
    {
        $optionArr = parent::getOrderOptions($product);
        $bundleOptions = [];

//        if ($product->hasCustomOptions()) {
//            $customOption = $product->getCustomOption('bundle_option_ids');
//            $optionIds = $this->serializer->unserialize($customOption->getValue());
//            $options = $this->getOptionsByIds($optionIds, $product);
//            $customOption = $product->getCustomOption('bundle_selection_ids');
//            $selectionIds = $this->serializer->unserialize($customOption->getValue());
//            $selections = $this->getSelectionsByIds($selectionIds, $product);
//            foreach ($selections->getItems() as $selection) {
//                if ($selection->isSalable()) {
//                    $selectionQty = $product->getCustomOption('selection_qty_' . $selection->getSelectionId());
//                    if ($selectionQty) {
//                        $price = $product->getPriceModel()
//                            ->getSelectionFinalTotalPrice(
//                                $product,
//                                $selection,
//                                0,
//                                $selectionQty->getValue()
//                            );
//
//                        $option = $options->getItemById($selection->getOptionId());
//                        if (!isset($bundleOptions[$option->getId()])) {
//                            $bundleOptions[$option->getId()] = [
//                                'option_id' => $option->getId(),
//                                'label' => $option->getTitle(),
//                                'value' => [],
//                            ];
//                        }
//
//                        $bundleOptions[$option->getId()]['value'][] = [
//                            'title' => $selection->getName(),
//                            'qty' => $selectionQty->getValue(),
//                            'price' => $this->priceCurrency->convert($price),
//                        ];
//                    }
//                }
//            }
//        }

        $optionArr['bundle_options'] = $bundleOptions;

        /**
         * Product Prices calculations save
         */
        if ($product->getPriceType()) {
            $optionArr['product_calculations'] = self::CALCULATE_PARENT;
        } else {
            $optionArr['product_calculations'] = self::CALCULATE_CHILD;
        }

        $optionArr['shipment_type'] = $product->getShipmentType();

        return $optionArr;
    }

    /**
     * Sort selections method for usort function
     *
     * Sort selections by option position, selection position and selection id
     *
     * @param  \Magento\Catalog\Model\Product $firstItem
     * @param  \Magento\Catalog\Model\Product $secondItem
     * @return int
     */
    public function shakeSelections($firstItem, $secondItem)
    {
        $aPosition = [
            $firstItem->getOption()
                ->getPosition(),
            $firstItem->getOptionId(),
            $firstItem->getPosition(),
            $firstItem->getSelectionId(),
        ];
        $bPosition = [
            $secondItem->getOption()
                ->getPosition(),
            $secondItem->getOptionId(),
            $secondItem->getPosition(),
            $secondItem->getSelectionId(),
        ];

        return $aPosition <=> $bPosition;
    }

    /**
     * Return true if product has options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasOptions($product)
    {
        $this->setStoreFilter($product->getStoreId(), $product);
        $optionIds = $this->getOptionsCollection($product)
            ->getAllIds();
        $collection = $this->getSelectionsCollection($optionIds, $product);

        if ($collection->getSize() > 0 || $product->getOptions()) {
            return true;
        }

        return false;
    }

    /**
     * Allow for updates of children qty's
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean true
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForceChildItemQtyChanges($product)
    {
        return true;
    }

    /**
     * Retrieve additional searchable data from type instance
     *
     * Using based on product id and store_id data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getSearchableData($product)
    {
        $searchData = parent::getSearchableData($product);

        $optionSearchData = $this->_bundleOption->create()
            ->getSearchableData(
                $product->getId(),
                $product->getStoreId()
            );
        if ($optionSearchData) {
            $searchData = array_merge($searchData, $optionSearchData);
        }

        return $searchData;
    }

    /**
     * Check if product can be bought
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkProductBuyState($product)
    {
        if (!$product->getSkipCheckRequiredOption() && $product->getHasOptions()) {
            $options = $product->getProductOptionsCollection();
            foreach ($options as $option) {
                if ($option->getIsRequire()) {
                    $customOption = $product->getCustomOption(self::OPTION_PREFIX . $option->getId());
                    if (!$customOption || strlen($customOption->getValue() ?? '') == 0) {
                        $product->setSkipCheckRequiredOption(true);
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('The product has required options. Enter the options and try again.')
                        );
                    }
                }
            }
        }

        return $this;
    }


    /**
     * Retrieve products divided into groups required to purchase
     *
     * At least one product in each group has to be purchased
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getProductsToPurchaseByReqGroups($product)
    {
        $groups = [];
        $allProducts = [];
        $hasRequiredOptions = false;
        foreach ($this->getOptions($product) as $option) {
            $groupProducts = [];
            foreach ($this->getSelectionsCollection([$option->getId()], $product) as $childProduct) {
                $groupProducts[] = $childProduct;
                $allProducts[] = $childProduct;
            }
            if ($option->getRequired()) {
                $groups[] = $groupProducts;
                $hasRequiredOptions = true;
            }
        }
        if (!$hasRequiredOptions) {
            $groups = [$allProducts];
        }

        return $groups;
    }

    /**
     * Prepare selected options for bundle product
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @param  \Magento\Framework\DataObject $buyRequest
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $option = $buyRequest->getBundleOption();
        $optionQty = $buyRequest->getBundleOptionQty();

        $option = is_array($option) ? array_filter($option, 'intval') : [];
        $optionQty = is_array($optionQty) ? array_filter($optionQty, 'intval') : [];

        $options = ['bundle_option' => $option, 'bundle_option_qty' => $optionQty];

        return $options;
    }

    /**
     * Check if product can be configured
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function canConfigure($product)
    {
        return $product instanceof \Magento\Catalog\Model\Product && $product->isAvailable() && parent::canConfigure(
                $product
            );
    }

    /**
     * Delete data specific for Bundle product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // @codingStandardsIgnoreStart
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
    }
    // @codingStandardsIgnoreEnd

    /**
     * Return array of specific to type product entities
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getIdentities(\Magento\Catalog\Model\Product $product)
    {
        $identities = [];
        $identities[] = parent::getIdentities($product);
        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($this->getOptions($product) as $option) {
            if ($option->getSelections()) {
                /** @var \Magento\Catalog\Model\Product $selection */
                foreach ($option->getSelections() as $selection) {
                    $identities[] = $selection->getIdentities();
                }
            }
        }

        return array_merge([], ...$identities);
    }

    /**
     * Returns selection qty
     *
     * @param \Magento\Framework\DataObject $selection
     * @param int[] $qtys
     * @param int $selectionOptionId
     * @return float
     */
    protected function getQty($selection, $qtys, $selectionOptionId)
    {
        if ($selection->getSelectionCanChangeQty() && isset($qtys[$selectionOptionId])) {
            if (is_array($qtys[$selectionOptionId]) && isset($qtys[$selectionOptionId][$selection->getSelectionId()])) {
                $selectionQty = $qtys[$selectionOptionId][$selection->getSelectionId()];
                $qty = (float)$selectionQty > 0 ? $selectionQty : 1;
            } else {
                $qty = (float)$qtys[$selectionOptionId] > 0 ? $qtys[$selectionOptionId] : 1;
            }
        } else {
            $qty = (float)$selection->getSelectionQty() ? $selection->getSelectionQty() : 1;
        }
        $qty = (float)$qty;

        return $qty;
    }

    /**
     * Returns qty
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $selection
     * @return float|int
     */
    protected function getBeforeQty($product, $selection)
    {
        $beforeQty = 0;
        $customOption = $product->getCustomOption('product_qty_' . $selection->getId());
        if ($customOption && $customOption->getProduct()->getId() == $selection->getId()) {
            $beforeQty = (float)$customOption->getValue();
            return $beforeQty;
        }

        return $beforeQty;
    }

    /**
     * Validate required options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $isStrictProcessMode
     * @param \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection
     * @param int[] $options
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkIsAllRequiredOptions($product, $isStrictProcessMode, $optionsCollection, $options)
    {
        if (!$product->getSkipCheckRequiredOption() && $isStrictProcessMode) {
            foreach ($optionsCollection->getItems() as $option) {
                if ($option->getRequired() && empty($options[$option->getId()])) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Please select all required options.')
                    );
                }
            }
        }
    }

    /**
     * Validate Options for Radio and Select input types
     *
     * @param Collection $optionsCollection
     * @param int[] $options
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function validateRadioAndSelectOptions($optionsCollection, $options): void
    {
        $errorTypes = [];

        if (is_array($optionsCollection->getItems())) {
            foreach ($optionsCollection->getItems() as $option) {
                if ($this->isSelectedOptionValid($option, $options)) {
                    $errorTypes[] = $option->getType();
                }
            }
        }

        if (!empty($errorTypes)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Option type (%types) should have only one element.',
                    ['types' => implode(", ", $errorTypes)]
                )
            );
        }
    }

    /**
     * Check if selected option is valid
     *
     * @param Option $option
     * @param array $options
     * @return bool
     */
    private function isSelectedOptionValid($option, $options): bool
    {
        return (
            ($option->getType() == 'radio' || $option->getType() == 'select') &&
            isset($options[$option->getOptionId()]) &&
            is_array($options[$option->getOptionId()]) &&
            count($options[$option->getOptionId()]) > 1
        );
    }

    /**
     * Check if selection is salable
     *
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $selections
     * @param bool $skipSaleableCheck
     * @param \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection
     * @param int[] $options
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkSelectionsIsSale($selections, $skipSaleableCheck, $optionsCollection, $options)
    {
        foreach ($selections->getItems() as $selection) {
            if (!$selection->isSalable() && !$skipSaleableCheck) {
                $_option = $optionsCollection->getItemById($selection->getOptionId());
                $optionId = $_option->getId();
                if (is_array($options[$optionId]) && count($options[$optionId]) > 1) {
                    $moreSelections = true;
                } else {
                    $moreSelections = false;
                }
                $isMultiSelection = $_option->isMultiSelection();
                if ($_option->getRequired() && (!$isMultiSelection || !$moreSelections)
                ) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The required options you selected are not available.')
                    );
                }
            }
        }
    }

    /**
     * Validate result
     *
     * @param array $_result
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkIsResult($_result)
    {
        if (is_string($_result)) {
            throw new \Magento\Framework\Exception\LocalizedException(__($_result));
        }

        if (!isset($_result[0])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t add this item to your shopping cart right now.')
            );
        }
    }

    /**
     * Merge selections with options
     *
     * @param \Magento\Catalog\Model\Product\Option[] $options
     * @param \Magento\Framework\DataObject[] $selections
     * @return \Magento\Framework\DataObject[]
     */
    protected function mergeSelectionsWithOptions($options, $selections)
    {
        $selections = [];

        foreach ($options as $option) {
            $optionSelections = $option->getSelections();
            if ($option->getRequired() && is_array($optionSelections) && count($optionSelections) == 1) {
                $selections[] = $optionSelections;
            } else {
                $selections = [];
                break;
            }
        }

        return array_merge([], ...$selections);
    }

    /**
     * Get prepared options with selection ids
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @param array $options
     * @return array
     */
    private function getPreparedOptions(array $options): array
    {
        foreach ($options as $optionId => $option) {
            foreach ($option as $selectionId => $optionQty) {
                $options[$optionId][$selectionId] = $selectionId;
            }
        }

        return $options;
    }
}
