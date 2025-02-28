<?php

namespace Ziffity\ProductCustomizer\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
use Magento\Framework\Registry;
use Ziffity\CustomFrame\Helper\Data as Helper;
use \Magento\Framework\Pricing\Helper\Data;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Ziffity\ProductCustomizer\Model\Components\Pricing\Postfinish;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Default Config Provider for customframe
 */
class PostFinishOptionConfigProvider implements ConfigProviderInterface
{

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var FramePricing
     */
    protected $framePricing;

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
    protected $optionsRepository;

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

    protected $customizerConfig = [];

    protected $selections = null;

    protected $pricing;

    protected $pricingHelper;

    protected $fromData = null;

    protected $storeManager;

    /**
     * @param ProductOptionRepositoryInterface $optionsRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Registry $registry
     * @param Helper $helper
     * @param FramePricing $framePricing
     * @param GalleryReadHandler $galleryReadHandler
     * @param Image $imageHelper
     */
    public function __construct(
        ProductOptionRepositoryInterface $optionsRepository,
        ProductRepositoryInterface $productRepository,
        Registry $registry,
        Helper $helper,
        GalleryReadHandler $galleryReadHandler,
        \Magento\Catalog\Helper\Image $imageHelper,
        Data $pricingHelper,
        Postfinish $pricing,
        StoreManagerInterface $storeManager
    ) {
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->imageHelper = $imageHelper;
        $this->pricingHelper = $pricingHelper;
        $this->pricing = $pricing;
        $this->storeManager = $storeManager;
    }

    /**
     * This function gets the post finish items from the config.
     *
     * @param array $value
     * @return array
     */
    public function getItems($value)
    {
        $productItems = [];
        if (isset($value['sku'])) {
            $this->setSku($value['sku']);
            $this->setOptions($value);
            return $this->getOptionsConfig();
        }
        return $productItems;
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
            $config['options']['post_finish'] = $option;
            return $config;
        }
        return [];
    }

    /**
     * @param $data
     * @return void
     */
    public function setFromData($data)
    {
        $this->fromData = $data;
    }

    public function prepareTab($product)
    {
        $options = [];
        $config = [];
        $visibleProducts = $this->optionsRepository->getList($this->getSku(), "primary", 'Post Finish');
        foreach ($visibleProducts as $key => $option) {
            if ($option->getTitle() == "Post Finish") {
                $config = [
                    'title'        => 'Post Finish',
                    'for_drawing'  => '1',
                    'order'        =>  12,
                    'position'     => $option->getPosition(),
                    'active_item' => $this->getActiveProduct($this->getSku())
                ];
            }
        }
        return $config;
    }

    public function getActiveProduct($sku)
    {
        $resultJson = [];
        $imgLayer = '';
        if(isset($this->fromData['post_finish']['active_item'])){
            return $this->fromData['post_finish']['active_item'];
        }
        $options = $this->optionsRepository->getList($sku, "primary", 'Post Finish');
        foreach ($options as $option) {
            if (isset($option)) {
                $product = $product ?? $this->productRepository->get($this->getSku());
                $selections = $product->getTypeInstance()->getSelectionsCollection([$option->getOptionId()], $product);
                $selections->getSelect()->order(['is_default DESC', 'position ASC']);
                $selections->setPageSize(1)->setCurPage(1);
                $defaultSelection = $selections->getFirstItem();
                $product = $this->productRepository->getById($defaultSelection->getProductId());
                if ($product) {
                    return $this->convertProduct($product);
                }
            }
        }
        return $resultJson;
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

    public function setOptions($value)
    {
        if (isset($value['options']) && isset($value['options']['size'])) {
            $this->selections = $value['options'];
        }
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
     * @param $config
     * @return void
     */
    public function setConfig($config)
    {
        $this->customizerConfig = $config;
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
     * @return array
     */
    public function getOptionsConfig()
    {
        $options = [];
        $visibleProducts = $this->optionsRepository->getList($this->getSku(), "primary", "Post Finish");
        foreach ($visibleProducts as $value) {
            if ($value->getTitle() == "Post Finish") {
                foreach ($value->getProductLinks() as $product) {
                    $productData = $this->productRepository->get($product->getSku());
//                    $product->setName($productData->getName());
//                    $product->setImage($this->loadProductImages($product, $productData));
//                    $product->setShortDescription($productData->getShortDescription());

//                    $selectionData = [
//                        'size' => [
//                            'type' => 'graphic', // frame|graphic
//                            'width' => [
//                                'integer' => 12,
//                                'tenth' => 0
//                            ],
//                            'height' => [
//                                'integer' => 14,
//                                'tenth' => 0
//                            ],
//                            'thickness' => 1
//                        ],
//                        'frame' => [
//                            'active_item' => [
//                                'id' => "123",
//                                // attribute data
//                                'width' => [
//                                    'integer' => '110',
//                                    'tenth' => '0'
//                                ],
//                                'height' => [
//                                    'integer' => '2',
//                                    'tenth' => '3/8'
//                                ],
//                                'back_of_moulding_width' => '0.1250'
//                            ]
//                        ],
//                    ];
                    $options['products'][] = $this->convertProduct($productData);
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
        $url = null;
        foreach ($productData->getMediaGalleryEntries() as $image) {
            // 'product_base_image' or any image code from vendor\magento\theme-frontend-luma\etc\view.xml
            $baseImage = $this->imageHelper->init($product, 'product_small_image')
                ->setImageFile($image->getFile())
                ->getUrl();
            $url = $baseImage;
        }
        return $url;
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

    public function convertProduct($product)
    {
        $resultJson = [];
        if ($product) {
            $imgThumb = $this->loadProductImages($product, $product);
            $selections = isset($this->customizerConfig['options']) ? $this->customizerConfig['options']: $this->selections;
            $price = $this->pricing->getPrice($product, $selections);

            $resultJson = [
                'id'        => $product->getId(),
//                'option_id' => $option->getId(),
//                'position'  => $option->getPosition(),
//                'title'     => $option->getTitle(),
//                'default_qty' => $selection->getQty(),
                // 'active'    => $activeState,
                'description' => $product->getDescription(),
                'code'      => $product->getCode(),
                'name'      => $product->getName(),
                'color'     => '#' . $product->getSupplier(),
                'price'     => $price,//$this->pricingHelper->currency($price, true, false),
                'img_thumb' => $imgThumb,
            ];
        }
        return $resultJson;
    }
}
