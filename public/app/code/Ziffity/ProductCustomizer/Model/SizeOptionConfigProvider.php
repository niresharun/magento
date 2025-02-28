<?php

namespace Ziffity\ProductCustomizer\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
use Magento\Framework\Registry;
use Ziffity\CustomFrame\Helper\Data as Helper;
use Ziffity\ProductCustomizer\Model\ResourceModel\Entity\Attribute\MultiSelectOptionValueProvider;


/**
 * Default Config Provider for customframe
 */
class SizeOptionConfigProvider implements ConfigProviderInterface
{

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

    /**
     * @var MultiSelectOptionValueProvider
     */
    protected $multiselectModel;

    protected $sku = null;

    protected $customizerConfig = null;

    protected $fromData = null;

    /**
     * @param ProductOptionRepositoryInterface $optionsRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Registry $registry
     * @param MultiSelectOptionValueProvider $multiselectModel
     * @param Helper $helper
     */
    public function __construct(
        ProductOptionRepositoryInterface $optionsRepository,
        ProductRepositoryInterface $productRepository,
        Registry $registry,
        MultiSelectOptionValueProvider $multiselectModel,
        Helper $helper
    ) {
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->multiselectModel = $multiselectModel;
        $this->helper = $helper;
    }

    /**
     * Get product
     *
     * @return string
     */
    public function getSku()
    {
        if ($this->sku == null) {
            return $this->registry->registry('current_product')->getSku();
        }
        return $this->sku;
    }

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
        if ($this->sku) {
            return $this->productRepository->get($this->sku);
        }
        return null;
    }

    /**
     * @param $config
     * @return void
     */
    public function setConfig($config)
    {
        $this->customizerConfig = $config;
    }

    public function prepareTab()
    {
        return null;
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

        $config = $this->getOptionsConfig();
        $product = $this->getProduct();
        if(!$product) {
            $product = $this->productRepository->get($this->getSku());
        }
        $config['options']['size'] = $this->getDefaultSize();
        $config['options']['size']['type'] = $this->getSizeType();

        $config['options']['size']['depth_type'] = $product->getDepthType();
        if($product->getDepthType() != 'none'){
            $availableThickness = [];
            if($product->getGraphicThicknessInteriorDepth()) {
                $thicknessValues = $this->multiselectModel->getMultiple($product->getGraphicThicknessInteriorDepth());
                $config['options']['size']['available_thickness'] = $thicknessValues;
                $config['options']['size']['thickness'] =
                    isset($availableThickness[0]) ? $availableThickness[0] : null;
            }
        }

        if(isset($this->fromData['size'])){
            $config['options']['size']['width']['integer'] = isset($this->fromData['size']['width']['integer']) ?
                $this->fromData['size']['width']['integer'] : $config['options']['size']['width']['integer'];
            $config['options']['size']['width']['tenth'] = isset($this->fromData['size']['width']['tenth']) ?
                $this->fromData['size']['width']['tenth'] : $config['options']['size']['width']['tenth'];
            $config['options']['size']['height']['integer'] = isset($this->fromData['size']['height']['integer']) ?
                $this->fromData['size']['height']['integer'] : $config['options']['size']['height']['integer'];
            $config['options']['size']['height']['tenth'] = isset($this->fromData['size']['height']['tenth']) ?
                $this->fromData['size']['height']['tenth'] : $config['options']['size']['height']['tenth'];
            if($product->getDepthType() != 'none'){
                $config['options']['size']['thickness'] = isset($this->fromData['options']['size']['thickness']) ?
                $this->fromData['options']['size']['thickness'] : $config['options']['size']['thickness'];
            }
            return $config;
        }
        return $config;
    }

    public function getDepth()
    {
        $depthType = 'none';
        $product  = $this->getProduct();
        if($product){
            $depthType = $product->getDepthType();
        }
        return $depthType;
    }

    /**
     * @return array
     */
    public function getOptionsConfig()
    {
        $options = [];
        $result['width'] = $this->getFractionalData('dimension_1');
        $result['height'] = $this->getFractionalData('dimension_2');
        $result['width'] = $this->fetchSizeList($result['width']);
        $result['height'] = $this->fetchSizeList($result['height']);
        $options['size_option']['fractionalData'] = $result;
        $options['size_option']['size_type'] = $this->getSizeType();
        return $options;
    }

    /**
     *
     *
     * @param $attributeValues array.
     *
     * @return array
     */
    public function fetchSizeList($attributeValues)
    {
        $values = [];
        if ($attributeValues) {
            $sortedValues = [];
            foreach ($attributeValues as $attributeValue) {
                $sortedValues[$attributeValue['value']] = $attributeValue;
            }
          //  ksort($sortedValues);
            foreach ($sortedValues as $key => $_value) {
                $decimal = $_value['value_label']['decimal'];
                if (!isset($values[$decimal])) {
                    $values[$decimal] = [
                        'integer' => $decimal,
                        'tenth'   => [],
                    ];
                }
                $fractional = 0;
                if ($_value['value_label']['fractional']['decimal']) {
                    $fractional = $_value['value_label']['fractional']['top']
                        . '/' . $_value['value_label']['fractional']['bottom'];
                }
                if (!in_array($fractional, $values[$decimal]['tenth'])) {
                    $values[$decimal]['tenth'][] = $fractional;
                }
            }
        }
        return array_values($values);
    }

    public function getFractionalData($attributeCode)
    {
        $data = [];
        if ($this->getProduct()) {
            $attributeValues = $this->getProduct()->getCustomAttribute($attributeCode)->getValue();
            $sizeValues = $this->multiselectModel->getMultiple($attributeValues);
            foreach ($sizeValues as $key => $sizeValue){
                $mfn = $this->helper->mixedFractionToNumber($sizeValue);
                $data[$key]['value_label'] = $mfn;
                $data[$key]['value'] = $sizeValue;
            }
        }
        return $data;
    }

    public function getDefaultSize()
    {
        $size = null;
        $product = $this->getProduct();
        $product = $product ? $product : $this->productRepository->get($this->getSku());
        if ($product && $product->getDimension1Default()) {
            $value =  $this->helper->floatToFractional($product->getDimension1Default()[0]);
            $size['width']['integer'] = $value['decimal'];
            $size['width']['tenth'] = (!$value['fractional']['top']) ? 0 :$value['fractional']['top'] .'/'. $value['fractional']['bottom'];
        } elseif ($product && $product->getDimension1()) {
            $value = $product->getResource()->getAttribute('dimension_1')->getSource()->getOptionText($product->getDimension1()[0]);
            $value =  $this->helper->mixedFractionToNumber($value);
            $size['width']['integer'] = $value['decimal'];
            $size['width']['tenth'] = (!$value['fractional']['top']) ? 0 :$value['fractional']['top'] .'/'. $value['fractional']['bottom'];
        }
        if ($product && $product->getDimension2Default()) {
            $value =  $this->helper->floatToFractional($product->getDimension2Default()[0]);
            $size['height']['integer'] = $value['decimal'];
            $size['height']['tenth'] = (!$value['fractional']['top']) ? 0 : $value['fractional']['top'] .'/'. $value['fractional']['bottom'];
        } elseif ($product && $product->getDimension2()) {
            $value = $product->getResource()->getAttribute('dimension_2')->getSource()->getOptionText($product->getDimension2()[0]);
            $value =  $this->helper->mixedFractionToNumber($value);
            $size['height']['integer'] = $value['decimal'];
            $size['height']['tenth'] = (!$value['fractional']['top']) ? 0 :$value['fractional']['top'] .'/'. $value['fractional']['bottom'];
        }
         return $size;
    }

    public function getSizeType()
    {
        $product = $this->getProduct();
        $product = $product ? $product : $this->productRepository->get($this->getSku());
        return strtolower($product->getAttributeText('size_type'));
    }
}
