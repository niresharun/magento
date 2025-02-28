<?php

namespace Ziffity\ProductCustomizer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;

class Frame extends AbstractHelper
{

    const  CO_PRODUCTS = "Co-Products";

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
     * @param ProductOptionRepositoryInterface $optionsRepository
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Context $context,
        ProductOptionRepositoryInterface $optionsRepository,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Registry $registry
    ) {
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        parent::__construct($context);
    }


    /**
     * To get progress bar data
     *
     * @return array
     */
    public function getOptionGroupItems()
    {
        $options = [];
        $product =  $this->getProduct();
        foreach ($this->optionsRepository->getList($product->getSku()) as $option) {
            if ($option->getTitle() !== self::CO_PRODUCTS) {
                array_push($options, $option->getTitle());
            }
        }
        return $options;
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
     * Get Frame Width
     *
     * @param Mage_Catalog_Model_Product|null $product Product Object.
     *
     * @return float
     */
    public function getFrameOverlap($product = null)
    {
        $width = 0;
        $overlap = 0;
        if (!$product) {
            $product = $this->getProduct();
        }

        if ($product) {
            $this->_getFrameOverlap($product);
        }

        return round((float) $width, 4);
    }

    public function _getFrameOverlap($product)
    {
        $graphicOverlap = $this->getMouldingWidth($json) - $this->_getBackOfMouldingWidth($json);
    }

    public function getMouldingWidth()
    {
        $mouldingWidth = 0;

        if (!empty($json['modules']['frame']['active_item']['id'])) {
            $mouldingWidth = $json['modules']['frame']['active_item']['img_draw']['height']['integer']
                . ' '
                . $json['modules']['frame']['active_item']['img_draw']['height']['tenth'];

            $mouldingWidth = $this->fractionalToFloat($mouldingWidth);
        }

        return round((float) $mouldingWidth, 4);
    }


    public function getBackOfMouldingWidth($productId)
    {
        $backOfMouldingWidth = '';
        $product = $this->productRepository->getById($productId);
        if ($product) {
            $backOfMouldingWidth = $product->getBackOfMouldingWidth();
        }
        return $backOfMouldingWidth;
    }


    public function getInnerFrameWidth($options)
    {
        $sizeType = "frame";
        $width = [];
        if (isset($options) && isset($options['size']['type'])) {
            $sizeType = $options['size']['type'];
            switch ($sizeType) {
                case 'frame':
                    $width = $this->getInnerFrameFrameWidth($options);
                    break;
                case 'graphic':
                    $width = $this->getInnerFrameGraphicWidth($options);
                    break;
                default:
                    $width = [];
            }
        }
        return $width;
    }

    public function getInnerFrameHeight($options)
    {
        $sizeType = "frame";
        $width = [];
        if (isset($options) && isset($options['size']['type'])) {
            $sizeType = $options['size']['type'];
            switch ($sizeType) {
                case 'frame':
                    $width = $this->getInnerFrameFrameHeight($options);
                    break;
                case 'graphic':
                    $width = $this->getInnerFrameGraphicHeight($options);
                    break;
                default:
                    $width = [];
            }
        }
        return $width;
    }

    public function getInnerFrameFrameWidth($options)
    {
        $width = $options['size']['selectedWidthInteger'].''.$options['size']['selectedWidthFractional'];
        return $this->fractionalToFloat($width);

    }

    public function getInnerFrameGraphicWidth($options)
    {
        //TODI calculate graphic width
        $graphicWidth = $this->getInnerFrameFrameWidth($options);

    }

    public function getInnerFrameFrameHeight($options)
    {
        $height = $options['size']['selectedHeightInteger'].''.$options['size']['selectedHeightFractional'];
        return $this->fractionalToFloat($height);

    }

    public function getInnerFrameGraphicHeight($options)
    {
        //TODI calculate graphic height
        $graphicHeight = $this->getInnerFrameFrameHeight($options);

    }

    /**
     * Convert formated fractional value to float.
     *
     * @param string $value Formatted fractional value.
     *
     * @return float
     */
    public function fractionalToFloat($value)
    {
        if (is_string($value)) {
            $value = preg_replace('/\s+/', ' ', $value);
            $value = trim($value);
            $value = explode(' ', $value);
            $value[0] = str_replace('\\', '/', trim($value[0]));
            $fractionalPart = 0;
            if (!empty($value[1])) {
                $value[1] = str_replace('\\', '/', trim($value[1]));
                $fractionalParts = explode('/', $value[1]);
                $fractionalPart = $fractionalParts[0] / $fractionalParts[1];
            }

            if (strpos($value[0], '/') !== false) {
                $value[0] = explode('/', $value[0]);
                $result = $value[0][0];
                if ($value[0][1] > 0) {
                    $result = floatval($value[0][0] / $value[0][1]);
                }
                $value[0] = $result;
            }

            $value = floatval($value[0]) + floatval($fractionalPart);
        }

        return $value;
    }

    /**
     * @return mixed
     */
    public function getAllIdsSorted($collection)
    {
        $ids = $collection->getSelect()->columns($collection->getIdFieldName());
        //clog('collection', json_encode($collection->getSelect()->__toString()));
        $ids = $collection->getConnection()->fetchCol($collection->getSelect());

        return $ids;
    }
}
