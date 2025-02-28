<?php
declare(strict_types=1);

namespace Ziffity\ProductCustomizer\Model\Components\Pricing;

use Ziffity\ProductCustomizer\Helper\Data as Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Lables
{
    const LABEL_TEXT_PRICE_CONFIG_PATH = 'custom_frame/component_price/lable_text_price';
    const LABEL_IMAGE_PRICE_CONFIG_PATH = 'custom_frame/component_price/lable_image_price';

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
    public function getConfigLableTextPrice()
    {
        return $this->scopeConfig->getValue(
            self::LABEL_TEXT_PRICE_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return float
     */
    public function getConfigLableImagePrice()
    {
        return $this->scopeConfig->getValue(
            self::LABEL_IMAGE_PRICE_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param  []$selection of current product.
     *
     * @return float
     */
    public function getPrice($selectionData)
    {
        $price = 0;
        $lableSizes = $this->getSizesForPriceProcessing($selectionData);
        if (isset($lableSizes['total_size_of_label_text_items'])) {
            $price += $lableSizes['total_size_of_label_text_items'] * $this->getConfigLableTextPrice();
        }
        if (isset($lableSizes['total_size_of_label_image_items'])) {
            $price +=  $lableSizes['total_size_of_label_image_items'] * $this->getConfigLableImagePrice();
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
    protected function getSizesForPriceProcessing($selectionData)
    {
        $lableSizes = [];
        if (!empty($selectionData['label'])) {
            $tabInfo = $selectionData['label'];

            if (isset($tabInfo['text-label']['textLabelArray'])) {
                $texts = $tabInfo['text-label']['textLabelArray'];
                if (count($texts)) {
                    $size = 0;
                    foreach ($texts as $text) {
                        $width = $this->helper->fractionalToFloat($text['width_inch']);
                        $height = $this->helper->fractionalToFloat($text['height_inch']);
                        $size += $width * $height;
                    }
                    $lableSizes['total_size_of_label_text_items'] = (float)$size;
                }
            }

            if (isset($tabInfo['image-label']['imageDataArray'])) {
                $images = $tabInfo['image-label']['imageDataArray'];
                if (count($images)) {
                    $size = 0;
                    foreach ($images as $image) {
                        $width = $this->helper->fractionalToFloat($image['width_inch']);
                        $height = $this->helper->fractionalToFloat($image['height_inch']);
                        $size += $width * $height;
                    }
                    $lableSizes['total_size_of_label_image_items'] = (float)$size;
                }
            }
        }
        return $lableSizes;
    }
}
