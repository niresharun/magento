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

/**
 * Shelves Config Provider for customframe
 */
class ShelvesConfigProvider implements ConfigProviderInterface
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

    protected $customizerConfig = [];

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    protected $fromData = null;

    /**
     *
     * @var Registry
     */
    protected $registry;

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
        FrameSize $frameSize
    ) {
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->imageHelper = $imageHelper;
        $this->frameSize = $frameSize;
    }

    public function getItems($value)
    {
        $this->setSku($value['sku']);
        $this->setOptions($value);
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
     * Return configuration array
     *
     * @return array|mixed
     */
    public function getConfig()
    {
        $product = $this->getProduct();
        if (!$product) {
            $product = $this->productRepository->get($this->getSku());
        }
//        $config['options']['size']['interior_depth'] = false;

        $option = $this->prepareShelves();
        if(!empty($option)) {
            $config['options']['shelves'] = $option;
//            $availableThickness = $product->getGraphicThicknessInteriorDepth();
//            if($availableThickness) {
//            $config['options']['size']['available_thickness'] = $availableThickness;
//            $config['options']['size']['thickness'] = $availableThickness[0];
//            }
            //$config['options']['shelves']['innerFrameWidth'] = $this->frameSize->getInnerFrameWidth($this->selections);
            return $config;
        }
        return [];
    }

    /**
     * @param $values
     * @return void
     */
    public function getOptionsConfig($values)
    {
        $config['options']['shelves']['frame_width'] = $this->frameSize->getInnerFrameWidth($this->selections);
        return $config;
    }

    /**
     * @return array
     */
    public function prepareShelves()
    {
        $options = [];
        $config = [];
        $config['available_shelves_qty'] = $this->getShelvesQty();
        $config['available_shelves_thickness'] = $this->getShelvesThickness();
        $config['form_data'] = $this->getFormData();
        return $config;
    }

    /**
     * @return array[]
     */
    public function getShelvesQty()
    {
        return [
            ['label' => '0', 'value'=> 0],
            ['label' => '1', 'value'=> 1],
            ['label' => '2', 'value'=> 2],
            ['label' => '3', 'value'=> 3],
            ['label' => '4', 'value'=> 4],
            ['label' => '5', 'value'=> 5],
            ['label' => '6', 'value'=> 6]
        ];
    }

    /**
     * @return \string[][]
     */
    public function getShelvesThickness()
    {
        return [
            ['label' => '1/4"', 'value' => '0.25'],
            ['label' => '3/8"', 'value' => '0.375']
            ];
    }

    protected function getFormData()
    {
        $formData = [
            0 => [
                'name'  => 'shelves_qty',
                'value' => '0',
            ],
            1 => [
                'name'  => 'shelves_thickness',
                'value' => '0.25',
            ],
        ];

        if (isset($this->fromData['shleves']['form_data'])) {
            return $this->fromData['shelves']['form_data'];
        }

        return $formData;
    }
}
