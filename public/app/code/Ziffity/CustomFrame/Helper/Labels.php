<?php

namespace Ziffity\CustomFrame\Helper;


use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Labels extends Mat
{

    /**
     * Retrieve product label info.
     *
     * @param \Magento\Catalog\Model\Product $product Product Object.
     *
     * @return array
     */
    public function getProductLabelsInfo($product)
    {
        $result = [];
        if ($product instanceof \Magento\Catalog\Model\Product) {
            $labelData = $product->getLabelData();
            $labelData = json_decode($labelData, true);
            if (empty($labelData)) {
                $labelData = [];
            }
            $result['position'] = $this->getPosition($labelData);
            $result['height'] = $this->getHeight($labelData);
            $result['width'] = $this->getWidth($labelData);
            $result['gap'] = $this->getGap();
            $result['fonts'] = $this->getFonts($labelData);
            $result['font_size_min'] = $this->getMinFontSize($labelData);
            $result['font_size_step'] = $this->getFontSizeStep($labelData);
            $result['font_size_default'] = $this->getDefaultFontSize($labelData);
            $result['text_colors'] = $this->getTextColors($labelData);
            $result['bg_colors'] = $this->getBgColors($labelData);
        }
        return $result;
    }

    /**
     * This function gets the gap in float format.
     *
     * @return mixed
     */
    public function getGap()
    {
        $gap = 2.5;
        return $this->fractionalToFloat($gap);
    }

    /**
     * This function gets the label data from the product processed in JSON format.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getLabelsJson(\Magento\Catalog\Model\Product $product)
    {
        $labelData = $product->getLabelData();
        if (empty($labelData)) {
            $labelData = "[]";
        }
        $labelData = json_decode($labelData, true);
        $labelJson = [
            'position'    => $this->getPosition($labelData),
            'size'        => [
                'height' => $this->formatFloatToFractional($this->getHeight($labelData)),
                'width'  => $this->formatFloatToFractional($this->getWidth($labelData)),
                'gap'    => $this->formatFloatToFractional($this->getGap())
            ],
            'fonts'       => $this->getLabelFonts($labelData),
            'font_conf'   => [
                'size_min_inch'  => $this->formatFloatToFractional($this->getMinFontSize($labelData)),
                'size_step_inch' => $this->formatFloatToFractional($this->getFontSizeStep($labelData)),
                'size_def_inch'  => $this->formatFloatToFractional($this->getDefaultFontSize($labelData)),
            ],
            'text_colors' => $this->getLabelTextColors($labelData),
            'bg_colors'   => $this->getLabelBgColors($labelData),
        ];
        if (!empty($labelData['texts'])) {
            $labelJson['texts'] = $labelData['texts'];
        }
        if (!empty($labelData['images'])) {
            $labelJson['images'] = $labelData['images'];
        }
        $labelJson = $this->addLabelImageUrlFromJson($labelJson);
        return $labelJson;
    }

    /**
     * This function adds the url for the image to be accessed from the browser.
     *
     * @param array $result
     * @return array
     * @throws NoSuchEntityException
     */
    public function addLabelImageUrlFromJson($result)
    {
        if (isset($result['images'])) {
            foreach ($result['images'] as $key => $data) {
                if (isset($data['url']) && strpos($data['url'], 'http') !== 0) {
                    $result['images'][$key]['url'] =
                        $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                        .'catalog/product/labels/'.$data['url'];
                }
            }
        }
        return $result;
    }

    /**
     * This function gets the bg colors for the label from the configurations.
     *
     * @param mixed $labelData
     * @return string|string[]
     */
    public function getLabelBgColors($labelData)
    {
        //TODO: Have to implement the configuration for loading the bg_colors from the configuration.
        $colors =
            ['#551a8b', '#FFA500', '#FFD700', '#000080'];
//        $colors = explode("\n", $colors);
        if (!empty($headerData['bg_colors'])) {
            $colors = implode(PHP_EOL, $headerData['bg_colors']);
        }
        return $colors;
    }

    /**
     * This function gets the label text colors from the configuration.
     *
     * @param mixed $labelData
     * @return string|string[]
     */
    public function getLabelTextColors($labelData)
    {
        $colors = $this->scopeConfig->getValue('label/label_configuration/label_text_color');
//        $colorOptions = explode(',', str_replace("'", '', $colors));

        $colorOptions = explode(PHP_EOL, $colors);

        $colorOptions = array_filter(array_map('trim', $colorOptions));


//        if (!empty($headerData['bg_colors'])) {
//            $colors = implode(PHP_EOL, $headerData['bg_colors']);
//        }

        return $colorOptions;
    }

    /**
     * This function gets the text_colors from the configurations.
     *
     * @param array $labelData
     * @return string|null
     */
    public function getTextColors($labelData)
    {
        $colors = $this->getLabelConfig('text_colors');

        if (!empty($labelData['text_colors'])) {
            $colors = implode(PHP_EOL, $labelData['text_colors']);
        }
        return $colors;
    }

    /**
     * This function gets the bg_colors from the configurations.
     *
     * @param array $labelData
     * @return string|null
     */
    public function getBgColors($labelData)
    {
        $colors = $this->getLabelConfig('bg_colors');
        if (!empty($labelData['bg_colors'])) {
            $colors = implode(PHP_EOL, $labelData['bg_colors']);
        }
        return $colors;
    }

    /**
     * This function gets the font_size_default from the configurations.
     *
     * @param array $labelData
     * @return mixed
     */
    public function getDefaultFontSize($labelData)
    {
        $size = $this->getLabelConfig('font_size_default');
        if (!empty($labelData['font_conf']['size_def_inch'])) {
            $size = $labelData['font_conf']['size_def_inch'];
        }
        return $this->fractionalToFloat($size);
    }

    /**
     * This function gets the font_size_step from the configurations.
     *
     * @param array $labelData
     * @return mixed
     */
    public function getFontSizeStep($labelData)
    {
        $size = $this->getLabelConfig('font_size_step');
        if (!empty($labelData['font_conf']['size_step_inch'])) {
            $size = $labelData['font_conf']['size_step_inch'];
        }
        return $this->fractionalToFloat($size);
    }

    /**
     * This function gets the font_size_min and processes from the configurations/
     *
     * @param array $labelData
     * @return mixed
     */
    public function getMinFontSize($labelData)
    {
        $size = $this->getLabelConfig('font_size_min');
        if (!empty($labelData['font_conf']['size_min_inch'])) {
            $size = $labelData['font_conf']['size_min_inch'];
        }
        return $this->fractionalToFloat($size);
    }

    /**
     * This function gets the fonts from the configurations.
     *
     * @param array $labelData
     * @return string|string[]
     */
    public function getLabelFonts($labelData)
    {
        $fonts = $this->getLabelConfig();
        if (!empty($fonts)) {
            return explode(',', $fonts);
        }
        if (!empty($labelData['fonts'])) {
            $fonts = implode(',', $labelData['fonts']);
        }

        return $fonts;
    }

    /**
     * This function gets the height from the configurations.
     *
     * @param array $labelData
     * @return mixed
     */
    public function getHeight($labelData)
    {
        $height = $this->getLabelConfig('height');
        if (!empty($labelData['size']['height'])) {
            $height = $labelData['size']['height'];
        }
        return $this->fractionalToFloat($height);
    }

    /**
     * This function gets the width from the configurations.
     *
     * @param array $labelData
     * @return mixed
     */
    public function getWidth($labelData)
    {
        $height = $this->getLabelConfig('width');
        if (!empty($labelData['size']['width'])) {
            $height = $labelData['size']['width'];
        }
        return $this->fractionalToFloat($height);
    }

    /**
     * This function gets the position and processes from the configuration.
     *
     * @param array $labelData
     * @return string|null
     */
    public function getPosition($labelData)
    {
        $pos = !empty($labelData['position']) ? $labelData['position'] : $this->getLabelConfig('position');
        return $pos;
    }

    /**
     * Retrieve labels config.
     *
     * @param string $field Config Field.
     *
     * @return string|null
     */
    public function getLabelConfig()
    {
        return $this->scopeConfig->getValue('label/label_configuration/label_fonts');
    }

    /**
     * Get Model Link Name
     *
     * @return integer
     */
    public function getLabelLinkName()
    {
        return 'labels';
    }

    /**
     * Prepare Glass tab json object
     *
     * @param \Magento\Catalog\Model\Product $product Product object.
     * @param string $productInfo Accessories product info.
     *
     * @return array|null
     */
    public function prepareLabelTab(\Magento\Catalog\Model\Product $product, $productInfo = null)
    {
        $labelData = $this->getLabelsJson($product);
        $tab = [
            'code' => 'labels',
            'url' => [
                'data' => $this->getLabelsDataUrl($product->getId()),
                'html' => $this->getLabelsHtmlUrl($product->getId())
            ],
            'for_drawing' => 1,
            'order' => 1100,
        ];
        $tab = array_merge($tab, $labelData);
        $text = [];
        if (!$productInfo && !empty($labelData['texts'])) {
            $text = $labelData['texts'];
        }
        if (!empty($productInfo['texts'])) {
            $text = $productInfo['texts'];
        }
        $tab['texts'] = $text;
        $images = [];
        if (!$productInfo && !empty($labelData['images'])) {
            $images = $labelData['images'];
        }
        if (!empty($productInfo['images'])) {
            $images = $productInfo['images'];
        }
        if (!empty($images)) {
            foreach ($images as &$image) {
                try {
                    if (strpos($image['url'], 'base64') !== false) {
                        $imagePath = $this
                            ->saveBase64Image($image['url'], 'labels', '', '');
                        $image['url'] = $imagePath;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            $tab['images'] = $this->addLabelImageUrl($images);
        }
        $tab = $this->buildUiLabelData($tab);
        return $tab;
    }

    /**
     * This function adds the url to the image file name and adds it in the array in param.
     *
     * @param array $result
     * @return array
     * @throws NoSuchEntityException
     */
    public function addLabelImageUrl($result)
    {
        foreach ($result as $key => $data) {
            if (isset($data['url']) && strpos($data['url'], 'http') !== 0) {
                $result[$key]['url'] =
                    $this->storeManager->getStore()->getBaseUrl().'pub/media/catalog/product/labels/'.$data['url'];
            }
        }
        return $result;
    }

    /**
     * This function builds the uiLabel data for the label from the tab details/
     *
     * @param array $tab
     * @return array
     */
    public function buildUiLabelData($tab)
    {
        if (isset($tab['position'])) {
            $tab['UiData']['position'] = $tab['position'];
        }
        if (isset($tab['size']['width'])) {
            $tab['UiData']['width'] = $this->fractionalToFloat($tab['size']['width']);
        }
        if (isset($tab['size']['height'])) {
            $tab['UiData']['height'] = $this->fractionalToFloat($tab['size']['height']);
        }
        if (isset($tab['font_conf']['size_min_inch'])) {
            $tab['UiData']['minimal_font_size'] = $this->fractionalToFloat($tab['font_conf']['size_min_inch']);
        }
        if (isset($tab['font_conf']['size_step_inch'])) {
            $tab['UiData']['font_size_step'] = $this->fractionalToFloat($tab['font_conf']['size_step_inch']);
        }
        if (isset($tab['font_conf']['size_def_inch'])) {
            $tab['UiData']['default_font_size'] = $this->fractionalToFloat($tab['font_conf']['size_def_inch']);
        }
        return $tab;
    }

    /**
     * Add tab values for rule processing
     *
     * @param \\Magento\Catalog\Model\Product $product Product object.
     *
     * @return $this
     */
    public function addValuesForRulesProcessing(\Magento\Catalog\Model\Product $product)
    {
        $productJson = $this->dataObject->getLabelCurrentJson($product);
        if (!empty($productJson['modules']['labels'])) {
            $tabInfo = $productJson['modules']['labels'];
            $texts = $tabInfo['texts'];
            if ($number = count($texts)) {
                $product->setData('number_of_label_text_items', $number);
                $size = 0;
                foreach ($texts as $text) {
                    $width = $this->fractionalToFloat($text['width_inch']);
                    $height = $this->fractionalToFloat($text['height_inch']);
                    $size += $width * $height;
                }
                $product->setData('total_size_of_label_text_items', $size);
            }
            $images = $tabInfo['images'];
            if ($number = count($images)) {
                $product->setData('number_of_label_image_items', $number);
                $size = 0;
                foreach ($images as $image) {
                    $width = $this->fractionalToFloat($image['width_inch']);
                    $height = $this->fractionalToFloat($image['height_inch']);
                    $size += $width * $height;
                }
                $product->setData('total_size_of_label_image_items', $size);
            }
        }
        return $this;
    }

    //TODO:Have to find the usage of this function in M1 and migrate this to M2.
//    /**
//     * Get calculated tab price
//     *
//     * @param \Magento\Catalog\Model\Product $product .
//     *
//     * @return float
//     */
//    public function getPrice(\Magento\Catalog\Model\Product $product)
//    {
//        /** @var Adg_Customizer_Model_Attribute_Process $ruleModel */
//        $ruleModel = Mage::getModel('adg_customizer/attribute_process');
//        $ruleModel->prepareProductOptions($product)->apply($product);
//        $price = (float)$product->getData('price_of_label_text_items.price');
//        $price += (float)$product->getData('price_of_label_image_items.price');
//
//        return $price;
//    }
}
