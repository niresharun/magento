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
use Ziffity\ProductCustomizer\Model\Calculation\Lighting\Wattage;
use Ziffity\ProductCustomizer\Model\Calculation\Lighting\PowerSupply;
use Ziffity\ProductCustomizer\Helper\Constants;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface;
use Ziffity\ProductCustomizer\Model\Components\Pricing\Lighting;
use Ziffity\ProductCustomizer\Model\ResourceModel\Entity\Attribute\MultiSelectOptionValueProvider;

/**
 * Shelves Config Provider for customframe
 */
class LightingConfigProvider implements ConfigProviderInterface
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

    protected $wattageCalculation;

    protected $powerSupplyCalculation;

    protected $selections = null;

    protected $customizerConfig = [];


    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Registry
     */
    protected $registry;

    protected  $scopeConfig;

    protected $pricing;

    protected $multiselectModel;

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
        Wattage $wattageCalculation,
        PowerSupply $powerSupplyCalculation,
        ScopeConfigInterface $scopeConfig,
        MultiSelectOptionValueProvider $multiselectModel,
        Lighting $pricing,

    ) {
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->imageHelper = $imageHelper;
        $this->frameSize = $frameSize;
        $this->wattageCalculation = $wattageCalculation;
        $this->powerSupplyCalculation = $powerSupplyCalculation;
        $this->scopeConfig = $scopeConfig;
        $this->multiselectModel = $multiselectModel;
        $this->pricing = $pricing;
    }

    public function getItems($value)
    {
        $this->setOptions($value);
        $this->setConfig($value);
        $this->setSku($value['sku']);
        return $this->getOptionsConfig($value);
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
     * @return array|mixed
     */
    public function getConfig()
    {
        $option = $this->prepareTab();
        if(!empty($option)) {
            $config['options']['lighting'] = $option;
            return $config;
        }
        return [];
    }

   public function getOptionsConfig()
   {
       $config = $this->getConfig();
       return $config;
   }

    public function prepareTab()
    {
        $product = $this->getProduct();
        $product = $product ?? $this->productRepository->get($this->getSku());
        if($product->getAdditionalTabs()) {
            $multiple = $this->multiselectModel->getMultiple($product->getAdditionalTabs());
            if(in_array('Lighting', $multiple)){
                $variables = $this->getVariables();
                $formData = $this->getFormData();

                if (!empty($formData['lighting_position']) && (($formData['lighting_position'] == 'top' && $variables['top_total_led_strip_wattage'] >= 60) ||
                        ($formData['lighting_position'] == 'perimeter' && $variables['perimeter_total_led_strip_wattage'] >= 60))
                ) {
                    $formData['power_connection'] = 'hardwired';
                }
                return [
                    'vars'          => $variables,
                    'for_drawing'   => '0',
                    'order'         => 0,
                    'form_data' => $formData,
                ];
            }
         }
        return [];
    }

    public function getVariables()
    {
        $options = !empty($this->fromData) ? $this->fromData : $this->customizerConfig['options'];
        $innerFrameWidth = $this->frameSize->getInnerFrameWidth($options);
        $innerFrameHeight = $this->frameSize->getInnerFrameHeight($options);

        $topLightWattage = $this->wattageCalculation->calculateTopLighting($innerFrameWidth);
        $topLightPowerSupply = $this->powerSupplyCalculation->calculatePowerSupply($topLightWattage);

        $perimeterLightWattage = $this->wattageCalculation->calculatePerimeterLighting($innerFrameWidth, $innerFrameHeight);
        $perimeterLightPowerSupply = $this->powerSupplyCalculation->calculatePowerSupply($perimeterLightWattage);

        $topLightingWattage = $topLightWattage * 1.2;
        $perimeterLightingWattage = $perimeterLightWattage * 1.2;

        $variables = [
            'top_total_led_strip_wattage'       => $topLightWattage,
            'top_led_strip_power_supply'        => $topLightPowerSupply,
            'perimeter_total_led_strip_wattage' => $perimeterLightWattage,
            'perimeter_led_strip_power_supply'  => $perimeterLightPowerSupply,
            'top_lighting_wattage'              => $topLightingWattage,
            'perimeter_lighting_wattage'        => $perimeterLightingWattage,
            'top_led_strip_price'              =>  $this->pricing->getLightingTopPrice($options),
            'perimeter_led_strip_price'       => $this->pricing->getLightingPerimeterPrice($options),
            'power_connection_price'          => $this->pricing->getConfigPowerConnectionPrice(),
            'power_connection_plug_price'     => $this->pricing->getConfigPowerConnectionPlugPrice(),
            'power_connection_hardwired_price'     => $this->pricing->getConfigPowerConnectionHardwiredPrice()


        ];
        return $variables;
    }


    /**
     * Get form data
     *
     * @param null|array $productInfo Product Info.
     *
     * @return array
     */
    protected function getFormData()
    {
        $formData = [
            'lighting_position' => 'top',
            'power_connection'  => 'hardwired',
            'cord_color'  => 'black',
        ];
        if($this->fromData && isset($this->fromData['lighting'])){
            return $this->fromData['lighting']['form_data'];
        }
        return $formData;
    }

}
