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
use Ziffity\ProductCustomizer\Model\IndexerCollectionFetchData;
use Ziffity\ProductCustomizer\Model\Components\Pricing\Corkboards;
use Magento\Store\Model\StoreManagerInterface;

/**
 * CorkBoardConfigProvider Provider for customframe
 */
class CorkBoardConfigProvider implements ConfigProviderInterface
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

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Registry
     */
    protected $registry;

    protected $customizerConfig = [];

    protected $pricingHelper;

    protected $pricing;

    protected  $selections = null;

    protected $storeManager;

    protected $fromData = null;

    /**
     * @param ProductOptionRepositoryInterface $optionsRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Registry $registry
     * @param Helper $helper
     * @param GalleryReadHandler $galleryReadHandler
     * @param Image $imageHelper
     * @param IndexerCollectionFetchData $indexerCollection
     */
    public function __construct(
        ProductOptionRepositoryInterface $optionsRepository,
        ProductRepositoryInterface $productRepository,
        Registry $registry,
        Helper $helper,
        GalleryReadHandler $galleryReadHandler,
        Image $imageHelper,
        IndexerCollectionFetchData $indexerCollection,
        Data $pricingHelper,
        Corkboards $pricing,
        StoreManagerInterface $storeManager
    ) {
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->imageHelper = $imageHelper;
        $this->indexerCollection = $indexerCollection;
        $this->pricingHelper = $pricingHelper;
        $this->pricing = $pricing;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $values
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
        $visibleProducts = $this->optionsRepository->getList($product->getSku(), "Corkboards");
        return $visibleProducts;
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
     * Return configuration array
     *
     * @return array|mixed
     */
    public function getConfig()
    {
        $product = $this->getProduct();
        $option = $this->prepareTab($product);
        if(!empty($option)) {
            $config['options']['cork_board'] = $option;
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
        $visibleProducts = $this->optionsRepository->getList($this->getSku(), "primary", 'Corkboards');
        foreach ($visibleProducts as $key => $value) {
            if ($value->getTitle() == "Corkboards") {
                foreach ($value->getProductLinks() as $product) {
                    $productData = $this->productRepository->get($product->getSku());
                    $options['products'][] = $this->convertProduct($productData);
                }
            }
            $options['product_total_count']['corkboards'] = $this->registry->registry($this->getSku());
        }
        if (isset($params['filters']) && $params['filters'] == "false"
            && isset($options['products'])) {
            $filterForSku = $this->indexerCollection
                ->getAllVisibleProductSku($this,"Corkboards");
            $options['filters'] = $this->indexerCollection
                ->renderFilters($filterForSku);
            $options['total_filters_count'] = count($options['filters']);
        }
        return $options;
    }

    public function convertProduct($product, $skipPrice = false)
    {
        $resultJson = [];
        if($product) {
            $imgThumb = $this->loadProductImage($product, null) ;
            $imgLayer = $product->getImgLayer() ? $this->loadProductImage($product, 'layer') : ''; // TODO layer image should be replaced with attribute value;
            $selections = isset($this->customizerConfig['options']) ? $this->customizerConfig['options']: $this->selections;
            //$price = $this->pricing->getPrice($product, $selections);

            $resultJson = [
                'id'        => $product->getId(),
                //'option_id' => $option->getId(),
                //'position'  => $option->getPosition(),
                //'title'     => $option->getTitle(),
                //'default_qty' => $selection->getQty(),
                // 'active'    => $activeState,
                'name'      => $product->getName(),
                'color'     => '#' . $product->getSupplier(),
                //'description' => $product->getDescription(),
                'price'     => $skipPrice ? '' : $this->pricing->getPrice($product, $selections),//$this->pricingHelper->currency($price, true, false),
                'img_thumb' => $imgThumb,
                'img_draw'  => [
                    'type' => 'image',
                    'src'    => $imgLayer
                ],
            ];
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
        $visibleProducts = $this->optionsRepository->getList($this->getSku(), "primary", 'Corkboards');
        foreach ($visibleProducts as $key => $option) {
            if ($option->getTitle() == "Corkboards") {
                $config = [
                    'title'        => 'Corkboards',
                    'for_drawing'  => '1',
                    'order'        =>  11,
                    'position'     => $option->getPosition(),
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
    public function getActiveProduct($sku, $skipPrice = false)
    {
        $resultJson = [];
        $imgLayer = '';
        if(isset($this->fromData['cork_board']['active_item'])){
            return $this->fromData['cork_board']['active_item'];
        }
        $options = $this->optionsRepository->getList($sku, "primary", 'Corkboards');
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
}
