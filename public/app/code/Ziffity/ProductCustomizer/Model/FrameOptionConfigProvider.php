<?php

namespace Ziffity\ProductCustomizer\Model;

use Exception;
use Ziffity\ProductCustomizer\Model\IndexerCollectionFetchData;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
use Magento\Framework\Registry;
use Ziffity\CustomFrame\Helper\Data as Helper;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use \Psr\Log\LoggerInterface;
use \Magento\Framework\Pricing\Helper\Data;
use Ziffity\ProductCustomizer\Model\Components\Pricing\Frame as FramePricing;
use Magento\Store\Model\StoreManagerInterface;
use Ziffity\ProductCustomizer\Model\Components\Measurements\FrameSize;

/**
 * Default Config Provider for customframe
 */
class FrameOptionConfigProvider implements ConfigProviderInterface
{

    protected $indexerCollection;

    protected $imageHelper;

    /**
     * @var GalleryReadHandler
     */
    protected $galleryReadHandler;

    /**
     * @var FramePricing
     */
    protected $framePricing;

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
     * @var Registry
     */
    protected $registry;

    protected $sku = null;

    protected $logger;

    protected $pricingHelper;

    protected $selections = null;

    protected $customizerConfig = null;

    protected $fromData = null;

    protected $storeManager;

    protected $frameHelper;

    public $pagination = null;

    public $searchQuery = null;

    public $filters = null;

    /**
     * @param ProductOptionRepositoryInterface $optionsRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Registry $registry
     * @param Helper $helper
     * @param FramePricing $framePricing
     * @param GalleryReadHandler $galleryReadHandler
     * @param Image $imageHelper
     * @param IndexerCollectionFetchData $indexerCollection
     */
    public function __construct(
        ProductOptionRepositoryInterface $optionsRepository,
        ProductRepositoryInterface $productRepository,
        Registry $registry,
        Helper $helper,
        FramePricing $framePricing,
        GalleryReadHandler $galleryReadHandler,
        \Magento\Catalog\Helper\Image $imageHelper,
        LoggerInterface $logger,
        Data $pricingHelper,
        IndexerCollectionFetchData $indexerCollection,
        StoreManagerInterface $storeManager,
        FrameSize $frameHelper
    ) {
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->framePricing = $framePricing;
        $this->helper = $helper;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->imageHelper = $imageHelper;
        $this->logger = $logger;
        $this->pricingHelper = $pricingHelper;
        $this->indexerCollection = $indexerCollection;
        $this->storeManager = $storeManager;
        $this->frameHelper = $frameHelper;
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
        $activeItemId = isset($value['active_item']) ? $value['active_item']: null;
        if (isset($value['sku']) && isset($value['pagination'])) {
            $this->setSku($value['sku']);
            $this->setPagination($value['pagination']);
            $this->setOptions($value);
            return $this->getOptionsConfig($value, $activeItemId);
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
     * @param $config
     * @return void
     */
    public function setConfig($config)
    {
        $this->customizerConfig = $config;
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
     * @param $value
     * @return void
     */
    public function setOptions($value)
    {
        if (isset($value['options']) && isset($value['options']['size'])) {
            //ToDo  frame calculation based on size option
            $this->selections = $value['options'];
        }
    }

    /**
     * Return configuration array
     * @return array|mixed
     */
    public function getConfig()
    {
        $product = $this->getProduct();
        $option = $this->prepareTab($product);
        if(!empty($option)) {
            $config['options']['frame'] = $option;
//            if(isset($config['options']['frame']['active_item'])) {
//                $product = $this->productRepository->getById($config['options']['frame']['active_item']['id']);
//                $config['options']['frame']['active_item']['price'] = $this->recalculatePrice($product, array_merge($config['options'], $this->customizerConfig['options']));
//            }
            return $config;
        }
        return [];
    }

    public function setFilters($filters)
    {
        $this->optionsRepository->filters = $filters;
        $this->filters = $filters;
    }

    public function recalculatePrice($product, $selections)
    {
        $price = $this->framePricing->getPrice($product, $selections);
        return $price;
    }

    /**
     * @return array
     */
    public function getOptionsConfig($params, $activeItemId = null)
    {
        $options = [];
        $count = 0;
        $sizeFilteredSku = [];
        if (isset($params['filters']) && $params['filters'] !=="false"){
            $this->setFilters($params['filters']);
        }
        $product = $this->productRepository->get($this->getSku());
        $visibleProducts = $this->optionsRepository->getList($this->getSku(), "primary", "Frame");
        foreach ($visibleProducts as $value) {
            if ($value->getTitle() == "Frame") {
                $productList = $this->getAvailableList($product,
                    $value->getOptionId(),
                    $params['options']);
                $count = $productList->getSize();
                $activeItemId = in_array($activeItemId, $productList->getAllIds()) ? $activeItemId : null;
                $productList = $product->getTypeInstance()->processSelections(
                    $productList, $product, $this->pagination, $this->searchQuery, $this->filters
                );

                if($activeItemId) {
                   // $subChildId = $this->getProductLinks($value->getProductLinks());
                    foreach ($productList as $product) {
                        // if (in_array($product->getId(),$subChildId)) {
                        $productData = $this->productRepository->get($product->getSku());
                        $options['products'][] = $this->convertProduct($productData);
                        $sizeFilteredSku[] = $product->getSku();
                        //}
                    }
                }
            }
        }
        $options['product_total_count'] = $count;
        $options['active_item'] = $activeItemId;
        if (isset($params['filters']) && $params['filters'] == "false"
            && isset($options['products'])) {
            $filterForSku = $this->indexerCollection
                ->getAllVisibleProductSku($this,"Frame",$sizeFilteredSku);
            $options['filters'] = $this->indexerCollection
                ->renderFilters($filterForSku);
            $options['total_filters_count'] = count($options['filters']);
        }
        return $options;
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
        $position = 1;
        $config = [];
        $options = $this->optionsRepository->getList($this->getSku(), "primary", "Frame");
        foreach ($options as $option) {
            if ($option->getTitle() == "Frame") {
                $config = [
                    'title'        => 'Frame',
                    'for_drawing'  => '1',
                    'position'      => $position,
                    'option_id'     => $option->getId(),
                    'active_item'  => $this->getActiveProduct($this->getSku(), true),
                ];
            }
        }
        return $config;
    }

    /**
     * Get available frames list
     *
     */
    public function getAvailableList($product, $optionId,$selections =  [])
    {
        $size = max($this->frameHelper->getInnerFrameWidth($selections), $this->frameHelper->getInnerFrameHeight($selections));
        $collection = $product->getTypeInstance()->getSelectionsCollection([$optionId], $product);
        $collection->addAttributeToFilter('layer_width', ['gteq' => 0]);
        $collection->addAttributeToFilter('layer_height', ['gteq' => 0]);
        $collection->getSelect()->where('(at_layer_width.value - at_layer_height.value * 2) >= ?', $size);
        return $collection;
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
     * @param $sku
     * @param $code
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getActiveProduct($sku, $skipPrice = false)
    {
        $resultJson = [];

        if(isset($this->fromData['frame']['active_item'])){
            return $this->fromData['frame']['active_item'];
        }

        $options = $this->optionsRepository->getList($sku, "primary", "Frame");
        foreach ($options as $option) {
            if (isset($option)) {
                $product = $this->getProduct();
                $product = $product ?? $this->productRepository->get($this->getSku());
                $selections = $product->getTypeInstance()->getSelectionsCollection([$option->getOptionId()], $product);
                $selections->getSelect()->order(['is_default DESC', 'position ASC']);
                $selections->setPageSize(1)->setCurPage(1);
                $defaultSelection = $selections->getFirstItem();
                $product = $this->productRepository->getById($defaultSelection->getProductId());
                if ($product) {
                    return $this->convertProduct($product, $skipPrice);
                }
            }
            return $resultJson;
        }
    }

    /**
     * @param $product
     * @param $productData
     * @return string|null
     */
    public function loadProductImage($product, $imageType)
    {
        $img = null;
        $imageId = 'product_small_image';
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        if($product) {
            if ($imageType == 'layer') {
                $imgFile = $mediaUrl.'catalog/product'.$product->getImgLayer();
                $imageId = 'layer_image';
            } else {
               // $imgFile = $mediaUrl.'catalog/product'.$product->getImgThumb();
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

    public function convertProduct($product, $skipPrice = false)
    {
        try {
            $width = $this->helper->floatToFractional($product->getLayerWidth());
            $height = $this->helper->floatToFractional($product->getLayerHeight());

            // TODO image
            $imgThumb = $this->loadProductImage($product, null) ;

            $backOfMouldingWidth = $product->getBackOfMouldingWidth();
            $widthOfMouldingForShell = $product->getWidthOfMouldingForShell();
            $selections = isset($this->customizerConfig['options']) ? $this->customizerConfig['options']: $this->selections;
            //$price = $this->framePricing->getPrice($product, $selections);

            try {
                $imgDraw = $this->loadProductImage($product, 'layer') ;
            } catch (\Exception $e) {
                $imgDraw = '';
            }

            $resultJson = [
                'id'        => $product->getId(),
                // 'default_qty' => $selection->getQty(),
                // 'active'    => $activeState,
                'name'      => $product->getName(),
                'color'     => '#' . $product->getColorLayer(),
//            'description' => $product->getShortDescription(),
                'price'     => $skipPrice ? '' : $this->framePricing->getPrice($product, $selections), //$this->pricingHelper->currency($price, true, false),
                'img_thumb' => $imgThumb,
                'img_draw'  => [
                    'src'    => $imgDraw, //TODO load data from layer image attribute
                    'width'  => [
                        'integer' => $width['decimal'],
                        'tenth'   => (!isset($width['fractional']['top'])) ? 0 : ($width['fractional']['top']
                            . '/' . $width['fractional']['bottom']),
                    ],
                    'height' => [
                        'integer' => $height['decimal'],
                        'tenth'   => (!isset($height['fractional']['top'])) ? 0 : ($height['fractional']['top']
                            . '/' . $height['fractional']['bottom']),
                    ],
                    'back_of_moulding_width' => $backOfMouldingWidth,
                    'width_of_moulding_for_shell' => $widthOfMouldingForShell,
                ],
            ];
        } catch (Exception $e) {
            $this->logger->debug($e->getMessage());
        }
        return $resultJson;
    }
}
