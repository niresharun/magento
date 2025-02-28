<?php

namespace Ziffity\ProductCustomizer\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
use Magento\Framework\Registry;
use Ziffity\CustomFrame\Helper\Data as Helper;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use \Magento\Catalog\Helper\Image;
use Ziffity\ProductCustomizer\Model\Components\Measurements\FrameSize;
use Ziffity\ProductCustomizer\Helper\Constants;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\View\Asset\Repository;
use \Magento\Framework\Pricing\Helper\Data;

/**
 * Shelves Config Provider for customframe
 */
class AddonsConfigProvider implements ConfigProviderInterface
{

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
    protected $optionsRepository;

    protected $sku = null;

    protected $frameSize;

    protected $selections = null;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     *
     * @var Registry
     */
    protected $registry;

    protected  $scopeConfig;

    protected $assetRepo;

    protected $customizerConfig = null;

    protected $pricingHelper;

    protected $fromData = null;

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
        FrameSize $frameSize,
        ScopeConfigInterface $scopeConfig,
        Repository $assetRepo,
        Data $pricingHelper
    ) {
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->imageHelper = $imageHelper;
        $this->frameSize = $frameSize;
        $this->scopeConfig = $scopeConfig;
        $this->assetRepo = $assetRepo;
        $this->pricingHelper = $pricingHelper;
    }


    /**
     * @param $value
     * @return void
     */
    public function setOptions($value)
    {
        if (isset($value['options']) && isset($value['options']['size'])) {
            $this->selections = $value['options'];
            //ToDo  frame calculation based on size option
        }
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
     * Return configuration array
     *
     * @return array|mixed
     */
    public function getConfig()
    {
        $storeScope = ScopeInterface::SCOPE_STORE;
        $config['options']['addons']['form_data']['plunge_lock']= 'no';
        $config['options']['addons']['form_data']['hinge_position'] = 'left';
        $config['options']['addons']['form_data']['plunge']['unit_price'] =
            $this->scopeConfig->getValue(Constants::PLUNGE_PRICING, $storeScope);
        if(isset($this->fromData['addons']['form_data'])){
            $config['options']['addons']['form_data']['plunge_lock']= $this->fromData['addons']['form_data']['plunge_lock'] ??
                $config['options']['addons']['form_data']['plunge_lock'];
            $config['options']['addons']['form_data']['hinge_position'] = $this->fromData['addons']['form_data']['hinge_position'] ??
                $config['options']['addons']['form_data']['plunge_lock'];
        }
        return $config;
    }

}
