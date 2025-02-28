<?php

namespace Ziffity\ProductCustomizer\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
use Magento\Framework\Registry;
use Ziffity\CustomFrame\Helper\Data as Helper;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Magento\Catalog\Model\Product\Attribute\Repository;
use \Magento\Catalog\Helper\Image;
use Ziffity\ProductCustomizer\Model\Components\Measurements\FrameSize;
use \Magento\Framework\Pricing\Helper\Data;
use Ziffity\ProductCustomizer\Model\Components\Pricing\Mat;
use Magento\Store\Model\StoreManagerInterface;
use Ziffity\CustomFrame\Helper\Mat as MatHelper;
use \Magento\Framework\Serialize\Serializer\Json;
use \Magento\Framework\View\Asset\Repository as AssetRepo;

/**
 * Default Config Provider for customframe
 */
class MatOptionConfigProvider implements ConfigProviderInterface
{

    protected $indexerCollection;

    protected $imageHelper;

    /**
     * @var GalleryReadHandler
     */
    protected $galleryReadHandler;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ProductOptionRepositoryInterface
     */
    public $optionsRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     *
     * @var Registry
     */
    protected $registry;

    protected $sku = null;

    protected $isMiddleMatEnabled = false;

    protected $selections = null;

    protected $isBottomMatEnabled = false;

    protected $customizerConfig = [];

    /**
     * @var Repository
     */
    protected $attributeRepository;

    /**
     * @var FrameSize
     */
    protected $frameModel;

    protected $pricingHelper;

    protected $matPricing;

    protected $storeManager;

    protected $fromData = null;

    protected $matHelper;

    protected $serializer;

    protected $assetRepo;

    public $pagination = null;

    public $searchQuery = null;

    public $filters = null;

    /**
     * @param ProductOptionRepositoryInterface $optionsRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Registry $registry
     * @param Helper $helper
     */
    public function __construct(
        ProductOptionRepositoryInterface $optionsRepository,
        ProductRepositoryInterface $productRepository,
        Registry $registry,
        Helper $helper,
        GalleryReadHandler $galleryReadHandler,
        Image $imageHelper,
        Repository $attributeRepository,
        FrameSize $frameModel,
        Data $pricingHelper,
        IndexerCollectionFetchData $indexerCollection,
        Mat $matPricing,
        StoreManagerInterface $storeManager,
        MatHelper $matHelper,
        Json $serializer,
        AssetRepo $assetRepo
    ) {
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->imageHelper = $imageHelper;
        $this->attributeRepository = $attributeRepository;
        $this->frameModel = $frameModel;
        $this->pricingHelper = $pricingHelper;
        $this->indexerCollection = $indexerCollection;
        $this->matPricing = $matPricing;
        $this->storeManager = $storeManager;
        $this->matHelper = $matHelper;
        $this->serializer = $serializer;
        $this->assetRepo = $assetRepo;
    }

    /**
     * @param $value
     * @return array
     */
    public function getItems($value): array
    {
        $productItems = [];
        if (isset($value['search'])) {
            $this->setSearch($value['search']);
        }
        $activeItemId = isset($value['active_item']) && isset($value['active_item']['id'])
            ? $value['active_item']['id']:null;
        if (isset($value['sku']) && isset($value['pagination']) && isset($value['optiontype'])) {
            $this->setSku($value['sku']);
            $this->setPagination($value['pagination']);
            $options = isset($value['options']) ? $value['options']: null;
            $this->setOptions($value);
            $products = $this->getOptionsConfig($value, $activeItemId);
            return $products;
        }
        return $productItems;
    }

    /**
     * Get product
     *
     * @return string
     */
    public function getSku()
    {
        if ($this->sku === null) {
            return $this->registry->registry('current_product')->getSku();
        }
        return $this->sku;
    }

    /**
     * Get product
     *
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * @param $sku
     * @return void
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @param $pagination
     * @return void
     */
    public function setPagination($pagination)
    {
        $this->optionsRepository->pagination = $pagination;
        $this->pagination = $pagination;
    }

    public function setFilters($filters)
    {
        $this->optionsRepository->filters = $filters;
        $this->filters = $filters;
    }

    /**
     * @param $data
     * @return void
     */
    public function setFromData($data)
    {
        $this->fromData = $data;
    }

    /**
     * @param $config
     * @return void
     */
    public function setConfig($config)
    {
        $this->customizerConfig = $config;
    }

    /**
     * Return configuration array
     *
     * @return array|mixed
     */
    public function getConfig()
    {
        $product = $this->getProduct();
        $option = $this->prepareTab($product);
        if(!empty($option)) {
            $config['options']['mat'] = $option;
            return $config;
        }
        return [];
    }

    /**
     * @param $value
     * @return void
     */
    public function setOptions($value)
    {
        if (isset($value['options']) && isset($value['options']['mat'])) {
            //ToDo  frame calculation based on size option
            $this->selections = $value['options'];
        }
    }

    /**
     * @param $searchQuery
     * @return void
     */
    public function setSearch($searchQuery)
    {
        $this->optionsRepository->searchQuery = $searchQuery;
        $this->searchQuery = $searchQuery;
    }

    /**
     * @return array
     */
    public function getOptionsConfig($option, $activeItemId = null, $tabs = null)
    {
        $options = [];
        $count = 0;
        $sizeFilteredSku = [];
        if (isset($option['filters']) && $option['filters'] !=="false"){
            $this->setFilters($option['filters']);
        }
        $parentProduct = $this->productRepository->get($option['sku']);
        $optionType = $option['optiontype'];
        $matType = str_replace("-", "_", $optionType);
        if ($optionType == "top-mat") {
            $topMat = $this->optionsRepository->getList($this->getSku(), "primary", "Top Mat", $activeItemId);
            foreach ($topMat as $mat) {
                if ($mat->getTitle() == 'Top Mat') {
                    if(isset($option['options']['mat']['active_items'][$matType]['id'])) {
                        $availableList = $this->getAvailableList($optionType, $parentProduct, $option['options']);
                        $count = $availableList->getSize();
                        $activeItemId = in_array($activeItemId, $availableList->getAllIds()) ? $activeItemId : null;
                        $availableList = $parentProduct->getTypeInstance()->processSelections(
                            $availableList, $parentProduct, $this->pagination, $this->searchQuery, $this->filters
                        );
                        if($activeItemId) {
                            $subChildId = $this->getProductLinks($mat->getProductLinks());
                            foreach ($availableList as $product) {
                                    $productData = $this->productRepository->get($product->getSku());
                                    $options['products']['top_mat'][] = $this->convertProduct($productData, $mat->getId());
                                    $sizeFilteredSku[] = $product->getSku();
                            }
                        }
                    }
                    if (isset($option['filters']) && $option['filters'] == "false"
                        && isset($options['products'])) {
                        $filterForSku = $this->indexerCollection
                            ->getAllVisibleProductSku($this, "Top Mat",$sizeFilteredSku);
                        $options['filters'] = $this->indexerCollection
                            ->renderFilters($filterForSku);
                        $options['total_filters_count'] = count($options['filters']);
                    }
                    $options['product_total_count']['top_mat'] = $count;
                    $options['active_item'] = $activeItemId;
                }
            }
        }
        if ($optionType == "middle-mat") {
            $middleMat = $this->optionsRepository->getList($this->getSku(), "primary", "Middle Mat");
            foreach ($middleMat as $mat) {
                if ($mat->getTitle() == 'Middle Mat') {
                    if (isset($option['options']['mat']['active_items'][$matType]['id'])) {
                        $availableList = $this->getAvailableList($optionType, $parentProduct, $option['options']);
                        $count = $availableList->getSize();
                        $activeItemId = in_array($activeItemId, $availableList->getAllIds()) ? $activeItemId : null;
                        $availableList = $parentProduct->getTypeInstance()->processSelections(
                            $availableList, $parentProduct, $this->pagination, $this->searchQuery, $this->filters
                        );
                        $subChildId = $this->getProductLinks($mat->getProductLinks());
                    }
                    if($activeItemId) {
                        $subChildId = $this->getProductLinks($mat->getProductLinks());
                        foreach ($availableList as $product) {
                            $productData = $this->productRepository->get($product->getSku());
                            $options['products']['middle_mat'][] = $this->convertProduct($productData, $mat->getId());
                            $sizeFilteredSku[] = $product->getSku();
                        }
                    }
                    if (isset($option['filters']) && $option['filters'] == "false"
                        && isset($options['products'])) {
                        $filterForSku = $this->indexerCollection
                            ->getAllVisibleProductSku($this, "Middle Mat",$sizeFilteredSku);
                        $options['filters'] = $this->indexerCollection
                            ->renderFilters($filterForSku);
                        $options['total_filters_count'] = count($options['filters']);
                    }
                    $options['product_total_count']['middle_mat'] = $count;
                    $options['active_item'] = $activeItemId;
                }
            }
        }
        if ($optionType == "bottom-mat") {
            $bottomMat = $this->optionsRepository->getList($this->getSku(), "primary", "Bottom Mat");
            foreach ($bottomMat as $mat) {
                if ($mat->getTitle() == 'Bottom Mat') {
                    if (isset($option['options']['mat']['active_items'][$matType]['id'])) {
                        $availableList = $this->getAvailableList($optionType, $parentProduct, $option['options']);
                        $count = $availableList->getSize();
                        $activeItemId = in_array($activeItemId, $availableList->getAllIds()) ? $activeItemId : null;
                        $availableList = $parentProduct->getTypeInstance()->processSelections(
                            $availableList, $parentProduct, $this->pagination, $this->searchQuery, $this->filters
                        );
                        $subChildId = $this->getProductLinks($mat->getProductLinks());
                    }
                    if($activeItemId) {
                        $subChildId = $this->getProductLinks($mat->getProductLinks());
                        foreach ($availableList as $product) {
                            $productData = $this->productRepository->get($product->getSku());
                            $options['products']['bottom_mat'][] = $this->convertProduct($productData, $mat->getId());
                            $sizeFilteredSku[] = $product->getSku();
                        }
                    }
                    if (isset($option['filters']) && $option['filters'] == "false"
                        && isset($options['products'])) {
                        $filterForSku = $this->indexerCollection
                            ->getAllVisibleProductSku($this, "Bottom Mat",$sizeFilteredSku);
                        $options['filters'] = $this->indexerCollection
                            ->renderFilters($filterForSku);
                        $options['total_filters_count'] = count($options['filters']);
                    }
                    $options['product_total_count']['bottom_mat'] = $count;
                    $options['active_item'] = $activeItemId;
                }
            }
        }
        if(isset($option['options']['mat']['active_items'][$matType]['id'])){
            $activeId = $option['options']['mat']['active_items']['top_mat']['id'];
            $availableList = $this->getAvailableList($optionType, $parentProduct, $option['options']);

            $productIds = $availableList->getAllIds();
            $options['error'] = false;
            if (!in_array($activeId, $productIds)) {
                $activeId = null;
                $options['error'] = true;
            }
        }
        return $options;
    }

    public function getProductLinks($productLinks)
    {
        $productId = [];
        foreach ($productLinks as $productLink)
        {
            $productId[] = $productLink->getEntityId();
        }
        return $productId;
    }

    /**
     * @param $product
     * @param $productData
     * @return string|null
     */
    public function loadProductImages($product, $productData)
    {
        $urls = null;
        foreach ($productData->getMediaGalleryEntries() as $image) {
            // 'product_base_image' or any image code from vendor\magento\theme-frontend-luma\etc\view.xml
            $baseImage = $this->imageHelper->init($product, 'product_small_image')
                ->setImageFile($image->getFile())
                ->getUrl();
            $urls = $baseImage;
        }
        return $urls;
    }

    /**
     * @param $product
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareTab($product)
    {
        $activeItems = [];
        $position = 3;
        $matCount = 0;
        $options = $this->optionsRepository->getList($this->getSku(), "primary");
        $product = $product ?? $this->productRepository->get($this->getSku());
        foreach ($options as $option) {
            if ($option->getTitle() == 'Top Mat') {
                $position = $option->getPosition();
                $matCount = 1;
                $activeItems['top_mat'] = $this->fromData['mat']['active_items']['top_mat'] ??
                    $this->getActiveProduct($this->getSku(), $option->getTitle(), true);
            }
            if ($option->getTitle() == 'Middle Mat') {
                $this->isMiddleMatEnabled = true;
                $matCount = 2;
                $activeItems['middle_mat'] = $this->fromData['mat']['active_items']['middle_mat'] ??
                    $this->getActiveProduct($product->getSku(), $option->getTitle(), true);
            }
            if ($option->getTitle() == 'Bottom Mat') {
                $this->isBottomMatEnabled = true;
                $matCount = 3;
                $activeItems['bottom_mat'] = $this->fromData['mat']['active_items']['bottom_mat'] ??
                    $this->getActiveProduct($product->getSku(), $option->getTitle(), true);
            }
        }
        if (!empty($activeItems)) {
            return  [
                'title'        => 'Mat',
                'for_drawing'  => '1',
                'position'     => $position,
                'sizes'        => $this->getCurrentSizes($product),
                'overlap'      => $product->getMatboardOverlap(),
                'mat_count'     => $matCount,
                'active_items' => $activeItems,
                'openings'     => false, // TODO openings
                'size_lock'    => null, //TODO size lock
            ];
        }
        return [];
    }

    /**
     * @param $sku
     * @param $code
     * @param $skipPrice
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getActiveProduct($sku, $code, $skipPrice = false)
    {
        $resultJson = [];
        $options = $this->optionsRepository->getList($sku, "primary", $code);
        foreach ($options as $option) {
            if (isset($option)) {
                $product = $product ?? $this->productRepository->get($this->getSku());
                $selections = $product->getTypeInstance()->getSelectionsCollection([$option->getOptionId()], $product);
                $selections->getSelect()->order(['is_default DESC', 'position ASC']);
                $selections->setPageSize(1)->setCurPage(1);
                $defaultSelection = $selections->getFirstItem();
                $product = $this->productRepository->getById($defaultSelection->getProductId());
                if ($product) {
                    return $this->convertProduct($product, $option->getId(), $skipPrice);
                }
            }
        }
        return $resultJson;
    }

    /**
     * @param $product
     * @param $productData
     * @return string|null
     */
    public function loadProductImage($product, $imageType)
    {
        $img = null;
        $imageId = 'product_thumbnail_image';
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        if($product) {
            if ($imageType == 'layer') {
                $imgFile = $mediaUrl.'catalog/product'.$product->getImgLayer();
                $imageId = 'layer_image';
            } else {
                $imgFile =  $this->imageHelper->init($product, $imageId)
                    ->setImageFile($product->getImgThumb())
                    ->getUrl();
            }
            // 'product_base_image' or any image code from vendor\magento\theme-frontend-luma\etc\view.xml
//            $img = $this->imageHelper->init($product, $imageId)
//                ->setImageFile($imgFile)
//                ->resize(380)
//                ->getUrl();
        }
        return $imgFile;
    }

    public function convertProduct($product, $optionId = null, $skipPrice = false)
    {
        $resultJson = [];
        if($product) {
            $width = $this->helper->floatToFractional($product->getLayerWidth());
            $height = $this->helper->floatToFractional($product->getLayerHeight());
            $selections = isset($this->customizerConfig['options']) ? $this->customizerConfig['options']: $this->selections;
//            $price = $this->matPricing->getPrice($product, $selections);
            $imgThumb = $this->loadProductImage($product, null) ;

            $type = 'pattern';
            $pattern = $product->getPatternMat();
            $imgDraw = $this->assetRepo->getUrl("Ziffity_ProductCustomizer::images/mats/".$pattern.".jpg");
            if ($pattern == 'paper') {
                $imgDraw = '';
            }
//            try {
//                $imgDraw = $this->loadProductImage($product, 'layer') ;
//            } catch (\Exception $e) {
//                $imgDraw = '';
//            }
            if ($product->getImgLayer()) {
                $imgDraw = $this->loadProductImage($product, 'layer') ; // TODO layer image should be replaced with attribute value;
                $type = 'image';
            }
            $resultJson = [
                'id'        => $product->getId(),
                'option_id' => $optionId,
                'supplier'  => $product->getSupplier(),
                'name'      => $product->getName(),
                'color'     => '#' . $product->getSupplier(),
                'color_layer' => '#' . $product->getColorLayer(),
                'price'     => $skipPrice ? '' :$this->matPricing->getPrice($product, $selections),//$this->pricingHelper->currency($price, true, false),
                'img_thumb' => $imgThumb,
                'img_draw'  => [
                    'type'   => $type, //"pattern" or "image"
                    'src'    => $imgDraw, //TODO load data from layer image attribute
                    'width'  => [
                        'integer' => $width['decimal'],
                        'tenth'   => (!isset($width['decimal']['top'])) ? 0 : ($width['decimal']['top']
                            . '/' . $width['decimal']['bottom']),
                    ],
                    'height' => [
                        'integer' => $height['decimal'],
                        'tenth'   => (!isset($height['decimal']['top'])) ? 0 : ($height['decimal']['top']
                            . '/' . $height['decimal']['bottom']),
                    ],
                ],
            ];
        }
        return $resultJson;
    }

    /**
     * @param $product
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentSizes($product)
    {
        //TODO update size based on selection
        if(isset($this->fromData['mat']['sizes'])){
            return $this->fromData['mat']['sizes'];
        }

        $sizes = [
            'top'    => [
                'integer' => '1',
                'tenth'   => '1/2',
            ],
            'reveal' => '0.25',
        ];

        if ($product->getCustomAttribute('opening_size')) {
            $openingSizes = $this->serializer->unserialize($product->getCustomAttribute('opening_size')->getValue());
            if ($openingSizes) {
                $sizes = $openingSizes;
                $sizes['sizes_lock'] = !empty($sizes['sizes_lock']) ? $sizes['sizes_lock'] : '';
            }
        }

        if (!$this->isMiddleMatEnabled && !$this->isBottomMatEnabled) {
            unset($sizes['reveal']);
        }
        return $sizes;
    }

    /**
     * @param $optionType
     * @param $product
     * @param $options
     * @return mixed
     */
    public function getAvailableList($optionType, $product, $options)
    {
        //$size = max($this->getWidth($options), $this->getHeight($options));
        $frameOverlap = $this->frameModel->getFrameOverlap($options);
        $frameWidth = $this->getWidth($options) + $frameOverlap * 2;
        $frameHeight = $this->getHeight($options) + $frameOverlap * 2;

        $max = max($frameWidth, $frameHeight);
        $min = min($frameWidth, $frameHeight);

        $matType = str_replace("-", "_", $optionType);
        $optionId = $options['mat']['active_items'][$matType]['option_id'];
        $collection = $product->getTypeInstance()->getSelectionsCollection([$optionId], $product);
        $collection->addAttributeToSelect(['mat_width', 'mat_height']);
        $collection->addAttributeToFilter('mat_width', ['gt' => 0]);
        $collection->addAttributeToFilter('mat_height', ['gt' => 0]);
             $collection->getSelect()->where(
                 'GREATEST(at_mat_width.value, at_mat_height.value) >= ' . $max . '
                 AND LEAST(at_mat_width.value, at_mat_height.value) >= ' . $min
             );

        return $collection;
    }

    /**
     * @param $options
     * @return float
     */
    public function getWidth($options)
    {
        $innerFrameWidth =  $this->frameModel->getInnerFrameWidth($options);
        return $innerFrameWidth;
    }

    /**
     * @param $options
     * @return float|int|string
     */
    public function getHeight($options)
    {
        $innerFrameHeight =  $this->frameModel->getInnerFrameHeight($options);
        return $innerFrameHeight;
    }
}
