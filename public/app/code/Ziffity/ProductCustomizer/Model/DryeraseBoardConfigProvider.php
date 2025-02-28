<?php

namespace Ziffity\ProductCustomizer\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
use Magento\Framework\Registry;
use Ziffity\CustomFrame\Helper\Data as Helper;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use \Magento\Catalog\Helper\Image;
use \Magento\Framework\Pricing\Helper\Data;
use Ziffity\ProductCustomizer\Model\Components\Pricing\Dryeraseboard;
use Ziffity\ProductCustomizer\Model\IndexerCollectionFetchData;
use Ziffity\ProductCustomizer\Model\Components\Pricing\Chalkboards;
use Magento\Store\Model\StoreManagerInterface;
/**
 * Calculation Provider for customframe
 */
class DryeraseBoardConfigProvider implements ConfigProviderInterface
{

    /**
     * @var IndexerCollectionFetchData
     */
    protected $indexerCollection;

    /**
     * @var Image
     */
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

    protected $sku = null;

    protected $customizerConfig = [];

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Registry
     */
    protected $registry;

    protected $pricingHelper;

    protected $selections = null;

    protected $pricing;

    protected $storeManager;

    protected $fromData = null;

    /**
     * @param ProductOptionRepositoryInterface $optionsRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Registry $registry
     * @param Helper $helper
     * @param GalleryReadHandler $galleryReadHandler
     * @param Image $imageHelper
     * @param Data $pricingHelper
     * @param IndexerCollectionFetchData $indexerCollection
     */
    public function __construct(
        ProductOptionRepositoryInterface $optionsRepository,
        ProductRepositoryInterface $productRepository,
        Registry $registry,
        Helper $helper,
        GalleryReadHandler $galleryReadHandler,
        Image $imageHelper,
        Data $pricingHelper,
        IndexerCollectionFetchData $indexerCollection,
        Dryeraseboard $pricing,
        StoreManagerInterface $storeManager
    ) {
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->imageHelper = $imageHelper;
        $this->pricingHelper = $pricingHelper;
        $this->indexerCollection = $indexerCollection;
        $this->pricing = $pricing;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $value
     * @return array
     */
    public function getItems($value)
    {
        $productItems = [];
        if (isset($value['search'])) {
            $this->setSearch($value['search']);
        }
        if (isset($value['sku']) && isset($value['pagination'])) {
            $this->setSku($value['sku']);
            $this->setPagination($value['pagination']);
            $this->setOptions($value);
            return $this->getOptionsConfig($value);
        }
        return $productItems;
    }

    /**
     * To get option group data
     *
     * @return array
     */
    public function getOptionGroupItems()
    {
        $options = [];
        $product =  $this->getProduct();
        $visibleProducts = $this->optionsRepository->getList($product->getSku(), "Dryerase Board");
        return $visibleProducts;
    }

    /**
     * @param $value
     * @return void
     */
    public function setOptions($value)
    {
        if (isset($value['options']) && isset($value['options']['size'])) {
            $this->selections = $value['options'];
        }
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
     * Get product
     *
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
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
     * @param $pagination
     * @return void
     */
    public function setPagination($pagination)
    {
        $this->optionsRepository->pagination = $pagination;
    }

    /**
     * @param $searchQuery
     * @return void
     */
    public function setSearch($searchQuery)
    {
        $this->optionsRepository->searchQuery = $searchQuery;
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
     * Return configuration array
     *
     * @return array|mixed
     */
    public function getConfig()
    {
        $product = $this->getProduct();
        $option = $this->prepareTab($product);
        if(!empty($option)) {
            $config['options']['dryerase_board'] = $option;
            return $config;
        }
        return [];
    }

    public function setFilters($filters)
    {
        $this->optionsRepository->filters = $filters;
    }

    /**
     * @return array
     */
    public function getOptionsConfig($params): array
    {
        $options = [];
        if (isset($params['filters']) && $params['filters'] !=="false"){
            $this->setFilters($params['filters']);
        }
        $visibleProducts = $this->optionsRepository->getList($this->getSku(), "primary", 'Dryerase Board');
        foreach ($visibleProducts as $key => $value) {
            if ($value->getTitle() == "Dryerase Board") {
                foreach ($value->getProductLinks() as $product) {
                    $productData = $this->productRepository->get($product->getSku());
                    $options['products'][] = $this->convertProduct($productData);
                }
            }
            $options['product_total_count']['dryerase_board'] = $this->registry->registry($this->getSku());
        }
        if (isset($params['filters']) && $params['filters'] == "false"
            && isset($options['products'])) {
            $filterForSku = $this->indexerCollection
                ->getAllVisibleProductSku($this,"Dryerase Board");
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
     * @return array|mixed
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareTab($product)
    {
        $options = [];
        $config = [];
        $visibleProducts = $this->optionsRepository->getList($this->getSku(), "primary", 'Dryerase Board');
        foreach ($visibleProducts as $key => $option) {
            if ($option->getTitle() == "Dryerase Board") {
                $config = [
                    'title'        => 'Dryerase Board',
                    'for_drawing'  => '1',
                    'order'        =>  12,
                    'position'     => $option->getPosition(),
                    'option_id'     => $option->getId(),
                    'active_item' => $this->getActiveProduct($this->getSku(), true)
                ];
            }
        }
        return $config;
    }

    /**
     * @param $sku
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getActiveProduct($sku, $skipPrice =  false)
    {
        $resultJson = [];
        $imgLayer = '';
        if(isset($this->fromData['dryerase_board']['active_item'])){
            return $this->fromData['dryerase_board']['active_item'];
        }
        $options = $this->optionsRepository->getList($sku, "primary", 'Dryerase Board');
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
        $imgFile = null;
        $imageId = 'product_small_image';
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

    public function convertProduct($product, $skipPrice = false)
    {
        $resultJson = [];
        if($product) {
            $width = $this->helper->floatToFractional($product->getLayerWidth());
            $height = $this->helper->floatToFractional($product->getLayerHeight());
            $imgThumb = $this->loadProductImage($product, null);
            $imgLayer = $product->getImgLayer() ? $this->loadProductImage($product, 'layer') : ''; // TODO layer image should be replaced with attribute value;

            $selections = isset($this->customizerConfig['options']) ? $this->customizerConfig['options']: $this->selections;
            //$price = $this->pricing->getPrice($product, $selections);

            $resultJson = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'color' => '#' . $product->getSupplier(),
                'description' => ($product->getDescription()),
                'price' => $skipPrice ? '' : $this->pricing->getPrice($product, $selections),//$this->pricingHelper->currency($price, true, false),
                'img_thumb' => $imgThumb,
                'img_draw' => [
                    'type' => 'image',
                    'src' => $imgLayer,
                    'width' => [
                        'integer' => $width['decimal'],
                        'tenth' => (!isset($width['decimal']['top'])) ? 0 : ($width['decimal']['top']
                            . '/' . $width['decimal']['bottom']),
                    ],
                    'height' => [
                        'integer' => $height['decimal'],
                        'tenth' => (!isset($height['decimal']['top'])) ? 0 : ($height['decimal']['top']
                            . '/' . $height['decimal']['bottom']),
                    ],
                ],
            ];
        }
        return $resultJson;
    }

}
