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
use Ziffity\ProductCustomizer\Model\Components\Pricing\Fabric;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\View\Asset\Repository as AssetRepo;

/**
 * Calculation Provider for customframe
 */
class FabricOptionConfigProvider implements ConfigProviderInterface
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

    /**
     * @var Data
     */
    protected $pricingHelper;

    protected $customizerConfig = [];

    protected $selections = null;

    protected $pricing;

    protected $storeManager;

    protected $fromData = null;

    protected $assetRepo;

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
        Fabric $pricing,
        StoreManagerInterface $storeManager,
        AssetRepo $assetRepo
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
        $this->assetRepo = $assetRepo;
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
        $visibleProducts = $this->optionsRepository->getList($product->getSku(), "Fabric");
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

    public function setFilters($filters)
    {
        $this->optionsRepository->filters = $filters;
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
            $config['options']['fabric'] = $option;
            return $config;
        }
        return [];
    }

    /**
     * @return array
     */
    public function getOptionsConfig($params)
    {
        $options = [];
        if (isset($params['filters']) && $params['filters'] !=="false"){
            $this->setFilters($params['filters']);
        }
        $visibleProducts = $this->optionsRepository->getList($this->getSku(), "primary", 'Fabric');
        foreach ($visibleProducts as $key => $value) {
            if ($value->getTitle() == "Fabric") {
                foreach ($value->getProductLinks() as $product) {
                    $productData = $this->productRepository->get($product->getSku());
                    $options['products'][] = $this->convertProduct($productData);
                }
            }
            $options['product_total_count']['fabric'] = $this->registry->registry($this->getSku());
        }
        if (isset($params['filters']) && $params['filters'] == "false"
            && isset($options['products'])) {
            $filterForSku = $this->indexerCollection
                ->getAllVisibleProductSku($this,"Fabric");
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
        $visibleProducts = $this->optionsRepository->getList($this->getSku(), "primary", 'Fabric');
        foreach ($visibleProducts as $key => $option) {
            if ($option->getTitle() == "Fabric") {
                $config = [
                    'title'        => 'Fabric',
                    'for_drawing'  => '1',
                    'order'        =>  10,
                    'position'     => $option->getPosition(),
                    'active_item' => $this->getActiveProduct($this->getSku(), true)
                ];
            }
        }
        return $config;
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
        }
        return $imgFile;
    }

    /**
     * @param $sku
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getActiveProduct($sku, $skipPrice= false)
    {
        $resultJson = [];
        if(isset($this->fromData['fabric_board']['active_item'])){
            return $this->fromData['fabric_board']['active_item'];
        }
        $options = $this->optionsRepository->getList($sku, "primary", 'Fabric');
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


    public function convertProduct($product, $skipPrice = false)
    {
        $resultJson = [];
        $imgLayer = '';
        $imgThumb = '';
        if($product) {
            $imgThumb = $this->loadProductImage($product, null);
            //$imgLayer = $product->getImgLayer() ? $this->loadProductImage($product, 'layer') : ''; // TODO layer image should be replaced with attribute value;

            $width = $this->helper->floatToFractional($product->getLayerWidth());
            $height = $this->helper->floatToFractional($product->getLayerHeight());
            $imgThumb = $this->loadProductImages($product, $product);
            $type = 'pattern';
            // TODO get pattern attribute
            $pattern = $product->getPattern();
            //$mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $imgLayer = $this->assetRepo->getUrl("Ziffity_ProductCustomizer::images/fabric/".$pattern.".jpg");
            //$imgLayer = $mediaUrl.'fabric/' . $pattern . '.jpg';

            $selections = isset($this->customizerConfig['options']) ? $this->customizerConfig['options']: $this->selections;
            $price = $this->pricing->getPrice($product, $selections);

            if ($product->getImgLayer()) {
                $imgLayer = $this->loadProductImage($product, 'layer'); // TODO load image layer
                $type = 'image';
            }

            $resultJson = [
                'id'        => $product->getId(),
//                'option_id' => $option->getId(),
//                'position'  => $option->getPosition(),
//                'title'     => $option->getTitle(),
//                'default_qty' => $selection->getQty(),
                // 'active'    => $activeState,
                'name'      => $product->getName(),
                'color'     => '#' . $product->getColorLayer(),
//                'description' => $product->getDescription(),
                'price'     => $skipPrice ? '' :$this->pricing->getPrice($product, $selections),//$this->pricingHelper->currency($price, true, false),
                'supplier'  => $product->getSupplier(),
                'img_thumb' => $imgThumb,
                'img_draw'  => [
                    'type' => $type,
                    'src'    => $imgLayer,
                    'color'  => '#' . $product->getColorLayer(),
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
                ],
            ];
        }
        return $resultJson;
    }
}
