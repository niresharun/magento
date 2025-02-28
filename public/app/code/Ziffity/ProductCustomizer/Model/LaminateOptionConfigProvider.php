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
use Ziffity\ProductCustomizer\Model\Components\Pricing\Laminate;
use \Magento\Framework\Pricing\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Default Config Provider for customframe
 */
class LaminateOptionConfigProvider implements ConfigProviderInterface
{

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

    protected $isLaminateExteriorEnabled = false;

    protected $isLaminateInteriorEnabled = false;

    protected $pricing;

    protected $selections = null;

    /**
     * @var Repository
     */
    protected $attributeRepository;

    /**
     * @var FrameSize
     */
    protected $frameModel;

    protected $customizerConfig = [];

    protected $pricingHelper;

    /**
     * @var
     */
    protected $pagination;

    protected $storeManager;

    protected $fromData = null;

    /**
     * @param ProductOptionRepositoryInterface $optionsRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Registry $registry
     * @param Helper $helper
     * @param GalleryReadHandler $galleryReadHandler
     * @param Image $imageHelper
     * @param Repository $attributeRepository
     * @param FrameSize $frameModel
     * @param IndexerCollectionFetchData $indexerCollection
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
        IndexerCollectionFetchData $indexerCollection,
        Laminate $pricing,
        Data $pricingHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->imageHelper = $imageHelper;
        $this->attributeRepository = $attributeRepository;
        $this->frameModel = $frameModel;
        $this->indexerCollection = $indexerCollection;
        $this->pricing = $pricing;
        $this->pricingHelper = $pricingHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $value
     * @return array
     */
    public function getItems($value): array
    {
        if (isset($value['search'])) {
            $this->setSearch($value['search']);
        }
        if (isset($value['sku']) && isset($value['optiontype']) && isset($value['pagination'])) {
            $this->setSku($value['sku']);
            $this->setPagination($value['pagination']);
            $options = isset($value['options']) ? $value['options']: null;
            $this->setOptions($value);
            $products = $this->getOptionsConfig($value, $options);
            return $products;
        }
        return [];
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

    public function setFilters($filters)
    {
        $this->optionsRepository->filters = $filters;
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

    public function setOptions($value)
    {
        if (isset($value['options']) && isset($value['options']['size'])) {
            $this->selections = $value['options'];
        }
    }

    /**
     * Return configuration array
     *
     * @return array|mixed
     */
    public function getConfig()
    {
        $product = $this->getProduct();
        if ($product) {
            $option = $this->prepareTab($product);
            if (!empty($option)) {
                $config['options']['laminate_finish'] = $option;
                return  $config;
            }
        }
        return [];
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
     * @return array
     */
    public function getOptionsConfig($option, $tabs = null)
    {
        $options = [];
        if (isset($option['filters']) && $option['filters'] !=="false"){
            $this->setFilters($option['filters']);
        }
        $optionType = $option['optiontype'];
        if ($optionType == "laminate-exterior") {
            $laminateExterior = $this->optionsRepository->getList($this->getSku(), "primary", "Laminate Exterior");
            foreach ($laminateExterior as $item) {
                foreach ($item->getProductLinks() as $product) {
                    $productData = $this->productRepository->get($product->getSku());
                    $options['products']['laminate_exterior'][] = $this->convertProduct($productData, 'Laminate Exterior');
                }
                $options['product_total_count']['laminate_exterior'] = $this->registry->registry($this->getSku());
                if (isset($option['filters']) && $option['filters'] == "false"
                    && isset($options['products'])) {
                    $filterForSku = $this->indexerCollection
                        ->getAllVisibleProductSku($this,"Laminate Exterior");
                    $options['filters'] = $this->indexerCollection
                        ->renderFilters($filterForSku);
                    $options['total_filters_count'] = count($options['filters']);
                }
            }
        }
        if ($optionType == "laminate-interior") {
            $laminateInterior = $this->optionsRepository->getList($this->getSku(), "primary", "Laminate Interior");
            foreach ($laminateInterior as $item) {
                foreach ($item->getProductLinks() as $product) {
                    $productData = $this->productRepository->get($product->getSku());
                    $options['products']['laminate_interior'][] = $this->convertProduct($productData, 'Laminate Interior');
                }
                $options['product_total_count']['laminate_interior'] = $this->registry->registry($this->getSku());
                if (isset($option['filters']) && $option['filters'] == "false"
                    && isset($options['products'])) {
                    $filterForSku = $this->indexerCollection
                        ->getAllVisibleProductSku($this,"Laminate Interior");
                    $options['filters'] = $this->indexerCollection
                        ->renderFilters($filterForSku);
                    $options['total_filters_count'] = count($options['filters']);
                }
            }
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
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareTab($product)
    {
        $activeItems = [];
        $position = 3;
        $options = $this->optionsRepository->getList($this->getSku(), "primary");
        foreach ($options as $option) {
            if ($option->getTitle() == 'Laminate Exterior') {
                $position = $option->getPosition();
                $activeItems['laminate_exterior'] = $this->getActiveProduct($product->getSku(), $option->getTitle());
            }
            if ($option->getTitle() == 'Laminate Interior') {
                $this->isLaminateExteriorEnabled = true;
                $activeItems['laminate_interior'] = $this->getActiveProduct($product->getSku(), $option->getTitle(), true);
            }
        }
        return  [
            'title'        => 'Laminate',
            'for_drawing'  => '1',
            'position'     => $position,
            'active_items' => $activeItems,
            'openings'     => false,
        ];
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
        if(isset($this->fromData['laminate_finish']['active_items'][$code])){
            return $this->fromData['laminate_finish']['active_items'][$code];
        }
        $options = $this->optionsRepository->getList($sku, "primary", $code);
        foreach ($options as $option) {
            $product = $this->getProduct();
            $product = $product ?? $this->productRepository->get($this->getSku());
            $selections = $product->getTypeInstance()->getSelectionsCollection([$option->getOptionId()], $product);
            $selections->getSelect()->order(['is_default DESC', 'position ASC']);
            $selections->setPageSize(1)->setCurPage(1);
            $defaultSelection = $selections->getFirstItem();
            $product = $this->productRepository->getById($defaultSelection->getProductId());
            if ($product) {
                $width = $this->helper->floatToFractional($product->getLayerWidth());
                $height = $this->helper->floatToFractional($product->getLayerHeight());

                $type = 'pattern';
                $pattern = $product->getPatternMat();

                if ($pattern == 'paper') {
                    $imgDraw = '';
                }

                if ($product->getImgLayer()) {
                    $imgDraw = 1; // TODO layer image should be replaced with attribute value;
                    $type = 'image';
                }
                return $this->convertProduct($product, $code, $skipPrice);
            }
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
        $sizes = [
            'top'    => [
                'integer' => '1',
                'tenth'   => '1/2',
            ],
            'reveal' => '1/2',
        ];

        $openingSizes = $this->attributeRepository->get('opening_size');
        if ($openingSizes) {
            unset($sizes['sizes_lock']);
        }

        if (!$this->isLaminateExteriorEnabled && !$this->isLaminateInteriorEnabled) {
            unset($sizes['reveal']);
        }
        return $sizes;
    }

    /**
     * @param $options
     * @return float
     */
    public function getWidth($options)
    {
        return $this->frameModel->getInnerFrameWidth($options);
    }

    /**
     * @param $options
     * @return float|int|string
     */
    public function getHeight($options)
    {
        return $this->frameModel->getInnerFrameHeight($options);
    }

    public function convertProduct($product, $code, $skipPrice = false)
    {
        $resultJson = [];
        if ($product) {
            $imgThumb = $this->loadProductImage($product, null);
            $width = $this->helper->floatToFractional($product->getLayerWidth());
            $height = $this->helper->floatToFractional($product->getLayerHeight());
            $imgLayer = $this->loadProductImage($product, 'layer');
            $selections = isset($this->customizerConfig['options']) ? $this->customizerConfig['options'] : $this->selections;
//            $price =  $this->pricing->getPrice($product, $code, $selections);
            //$price = $this->pricing->getPrice($product, $selections);

            $resultJson = [
                'id' => $product->getId(),

                'code' => $product->getCode(),
                'name' => $product->getName(),
                'color' => '#' . $product->getSupplier(),
                'price' => $skipPrice ? '' :$this->pricing->getPrice($product, $code, $selections),
                'img_thumb' => $imgThumb,
                'img_draw' => [
                    'type' => 'image', //"pattern" or "image"
                    'src' => $imgLayer, //TODO load data from layer image attribute
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
