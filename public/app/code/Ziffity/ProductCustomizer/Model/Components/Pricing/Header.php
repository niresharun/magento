<?php
declare(strict_types=1);

namespace Ziffity\ProductCustomizer\Model\Components\Pricing;

use Ziffity\ProductCustomizer\Helper\Data as Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Header
{
    const HEADER_TEXT_PRICE_CONFIG_PATH = 'custom_frame/component_price/header_text_price';
    const HEADER_IMAGE_PRICE_CONFIG_PATH = 'custom_frame/component_price/header_image_price';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Helper $helper
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Helper $helper,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return float
     */
    public function getConfigHeaderTextPrice()
    {
        return $this->scopeConfig->getValue(
            self::HEADER_TEXT_PRICE_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return float
     */
    public function getConfigHeaderImagePrice()
    {
        return $this->scopeConfig->getValue(
            self::HEADER_IMAGE_PRICE_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param  []$selectionData of current product.
     *
     * @return float
     */
    public function getPrice($selectionData)
    {
        $price = 0;
        $lableSizes = $this->getSizesForPriceProcessing($selectionData);
        if (isset($lableSizes['total_size_of_header_text_items'])) {
            $price += $lableSizes['total_size_of_header_text_items'] * $this->getConfigHeaderTextPrice();
        }
        if (isset($lableSizes['total_size_of_header_image_items'])) {
            $price +=  $lableSizes['total_size_of_header_image_items'] * $this->getConfigHeaderImagePrice();
        }
        return $price;
    }

    /**
     * Add tab values for rule processing
     *
     * @param [] $selectionData Product object.
     *
     * @return []
     */
    public function getSizesForPriceProcessing($selectionData)
    {
        $lableSizes = [];
        if (!empty($selectionData['header'])) {
            $tabInfo = $selectionData['header'];
            if (isset($tabInfo['text-header']['textHeaderArray'])) {
                $texts = $tabInfo['text-header']['textHeaderArray'];
                if (count($texts)) {
                    $size = 0;
                    foreach ($texts as $text) {
                        $width = $this->helper->fractionalToFloat($text['width_inch']);
                        $height = $this->helper->fractionalToFloat($text['height_inch']);
                        $size += $width * $height;
                    }
                    $lableSizes['total_size_of_header_text_items'] = (float)$size;
                }
            }

            if (isset($tabInfo['image-header']['imageDataArray'])) {
                $images = $tabInfo['image-header']['imageDataArray'];
                if (count($images)) {
                    $size = 0;
                    foreach ($images as $image) {
                        $width = $this->helper->fractionalToFloat($image['width_inch']);
                        $height = $this->helper->fractionalToFloat($image['height_inch']);
                        $size += $width * $height;
                    }
                    $lableSizes['total_size_of_header_image_items'] = (float)$size;
                }
            }
        }
        return $lableSizes;
    }
}
