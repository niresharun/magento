<?php

namespace Ziffity\CustomFrame\Helper;

use Magento\Framework\Exception\NoSuchEntityException;


/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Headers extends \Ziffity\CustomFrame\Helper\Url
{
    /**
     * Retrieve product header info.
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     */
    public function getProductHeadersInfo($product)
    {
        $result = [];
        $headerData = $product->getHeaderData();
        $headerData = json_decode($headerData, true);
        if (empty($headerData)) {
            $headerData = [];
        }
        $result['position'] = $this->getPosition($headerData);
        $result['headers_height'] = $this->getHeight($headerData);
        $result['headers_width'] = $this->getWidth($headerData);
        $result['fonts'] = $this->getFonts($headerData);
        $result['font_size_min'] = $this->getMinFontSize($headerData);
        $result['font_size_step'] = $this->getFontSizeStep($headerData);
        $result['font_size_default'] = $this->getDefaultFontSize($headerData);
        $result['text_colors'] = $this->getTextColors($headerData);
        $result['bg_colors'] = $this->getBgColors($headerData);
        return $result;
    }

    /**
     * This function returns the header json data processed from the product header_data attribute value.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getHeadersJson($product)
    {
        $headerData = $product->getHeaderData();
        if (empty($headerData)) {
            $headerData = [];
        }
        if (is_string($headerData)) {
            $headerData = json_decode($headerData, true);
        }

        $headerJson = [
            'position'    => $this->getPosition($headerData),
            'size'        => [
                'height' => $this->formatFloatToFractional($this->getHeight($headerData)),
                'width'  => $this->formatFloatToFractional($this->getWidth($headerData)),
            ],
//            'fonts'       => preg_split('#\s*,\s*#', $this->getFonts($headerData), null, PREG_SPLIT_NO_EMPTY),
            'fonts'       => explode(",", $this->getFonts($headerData)),
            'font_conf'   => [
                'size_min_inch'  => $this->formatFloatToFractional($this->getMinFontSize($headerData)),
                'size_step_inch' => $this->formatFloatToFractional($this->getFontSizeStep($headerData)),
                'size_def_inch'  => $this->formatFloatToFractional($this->getDefaultFontSize($headerData)),
            ],
//            'text_colors' => preg_split('#\s*\R\s*#', $this->getTextColors($headerData), null, PREG_SPLIT_NO_EMPTY),
            'text_colors' => $this->getTextColors($headerData),
//            'bg_colors'   => preg_split('#\s*\R\s*#', $this->getBgColors($headerData), null, PREG_SPLIT_NO_EMPTY)
            'bg_colors'   => $this->getBgColors($headerData)
        ];

        if (!empty($headerData['texts'])) {
            $headerJson['texts'] = $headerData['texts'];
        }

        if (!empty($headerData['images'])) {
            $headerJson['images'] = $headerData['images'];
        }

        if (!empty($headerData['bg_color_active'])) {
            $headerJson['bg_color_active'] = $headerData['bg_color_active'];
        }

        $headerJson = $this->addHeaderImageUrl($headerJson);

        return $headerJson;
    }

    /**
     * This function adds the url for the image to be accessed from the browser.
     *
     * @param array $result
     * @return array
     * @throws NoSuchEntityException
     */
    public function addHeaderImageUrl($result)
    {
        if (isset($result['images'])) {
            foreach ($result['images'] as $key => $data) {
                if (isset($data['url']) && strpos($data['url'], 'http') !== 0) {
                    $result['images'][$key]['url'] =
                        $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                        .'catalog/product/headers/'.$data['url'];
                }
            }
        }
        return $result;
    }

    /**
     * This function gets the header data from the configurations.
     *
     * @param array $headerData
     * @return string|string[]|null
     */
    public function getTextColors($headerData)
    {
        $colors = $this->scopeConfig->getValue('header/header_configuration/header_text_color');
//        $colorOptions = explode(',', str_replace("'", '', $colors));

        $colorOptions = explode(PHP_EOL, $colors);

        $colorOptions = array_filter(array_map('trim', $colorOptions));

//        if (!empty($headerData['text_colors'])) {
//            $colors = implode(PHP_EOL, $headerData['text_colors']);
//        }

        return $colorOptions;
    }

    /**
     * This function gets the bg colors from the configuration.
     *
     * @param array $headerData
     * @return string|string[]|null
     */
    public function getBgColors($headerData)
    {
        $colors = $this->scopeConfig->getValue('header/header_configuration/header_bg_color');
//        $colorOptions = explode(',', str_replace("'", '', $colors));

        $colorOptions = explode(PHP_EOL, $colors);

        $colorOptions = array_filter(array_map('trim', $colorOptions));

//        if (!empty($headerData['bg_colors'])) {
//            $colors = implode(PHP_EOL, $headerData['bg_colors']);
//        }

        return $colorOptions;
    }

    /**
     * This functions processes the default font size default from the header configuration.
     *
     * @param array $headerData
     * @return mixed
     */
    public function getDefaultFontSize($headerData)
    {
        //TODO:Add stores->configuration for font_size_default
//        $size = $this->getHeaderConfig('font_size_default');
        $size = '2';
        if (!empty($headerData['font_conf']['size_def_inch'])) {
            $size = $headerData['font_conf']['size_def_inch'];
        }
        return $this->fractionalToFloat($size);
    }

    /**
     * This function processed the font size step from the header configuration.
     *
     * @param array $headerData
     * @return mixed
     */
    public function getFontSizeStep($headerData)
    {
        //TODO:Add stores->configuration for font_size_step
//        $size = $this->getHeaderConfig('font_size_step');
        $size = '0.125';
        if (!empty($headerData['font_conf']['size_step_inch'])) {
            $size = $headerData['font_conf']['size_step_inch'];
        }
        return $this->fractionalToFloat($size);
    }

    /**
     * This function processes the font size from the header config for font_size_min
     *
     * @param array $headerData
     * @return mixed
     */
    public function getMinFontSize($headerData)
    {
        //TODO:Add stores->configuration for font_size_min
//        $size = $this->getHeaderConfig('font_size_min');
        $size = '0.125';
        if (!empty($headerData['font_conf']['size_min_inch'])) {
            $size = $headerData['font_conf']['size_min_inch'];
        }
        return $this->fractionalToFloat($size);
    }

    /**
     * This function gets the fonts configuration.
     *
     * @param array $headerData
     * @return string|null
     */
    public function getFonts($headerData)
    {
        $fonts = $this->getHeaderConfig();
        if (!empty($headerData['fonts'])) {
            $fonts = implode(',', $headerData['fonts']);
        }

        return $fonts;
    }

    /**
     * This function gets the height from the configuration.
     *
     * @param array $headerData
     * @return mixed
     */
    public function getHeight($headerData)
    {
        $height = $this->getHeaderConfig('height');
        if (!empty($headerData['size']['height'])) {
            $height = $headerData['size']['height'];
        }
        return $this->fractionalToFloat($height);
    }

    /**
     * This function gets the width from the configuration.
     *
     * @param array $headerData
     * @return mixed
     */
    public function getWidth($headerData)
    {
        $height = $this->getHeaderConfig('width');
        if (!empty($headerData['size']['width'])) {
            $height = $headerData['size']['width'];
        }
        return $this->fractionalToFloat($height);
    }

    /**
     * This function gets the position from the configuration.
     *
     * @param array $headerData
     * @return string|null
     */
    public function getPosition($headerData)
    {
        $pos = !empty($headerData['position']) ? $headerData['position'] : $this->getHeaderConfig('position');
        return $pos;
    }

    /**
     * Retrieve headers config.
     *
     * @param string $field Config Field.
     *
     * @return string|null
     */
    public function getHeaderConfig()
    {
//        if ($field == 'fonts') {
//            return 'Alegreya SC,Alfa Slab One,Cantata One,Cinzel,Farsan,Fira Sans Condensed,Hammersmith One,Josefin Slab,Julius Sans One,Lora,Montserrat,Noticia Text,Oswald,PT Sans,Pacifico,Playfair Display,Quattrocento,Quicksand,Raleway,Sacramento';
//        }
        return $this->scopeConfig->getValue('header/header_configuration/header_fonts');
    }

    /**
     * Get Model Link Name
     *
     * @return integer
     */
    public function getHeaderLinkName()
    {
        return 'crheader';
    }

    /**
     * Prepare Glass tab json object
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $productInfo
     * @return array|null
     */
    public function prepareHeaderTab(\Magento\Catalog\Model\Product $product, $productInfo = null)
    {
        $headerData = $this->getHeadersJson($product);
        $tab = [
            'code'        => 'crheader',
            'url'         => [
                'data' => $this->getHeadersDataUrl($product->getId()),
                'html' => $this->getHeadersHtmlUrl($product->getId())
            ],
            'for_drawing' => 1,
            'order'       => 1100
        ];
        $tab = array_merge($tab, $headerData);
        $text = [];
        if (!$productInfo && !empty($headerData['texts'])) {
            $text = $headerData['texts'];
        }
        if (!empty($productInfo['texts'])) {
            $text = $productInfo['texts'];
        }
        $tab['texts'] = $text;
        $images = [];
        if (!$productInfo && !empty($headerData['images'])) {
            $images = $headerData['images'];
        }
        if (!empty($productInfo['images'])) {
            $images = $productInfo['images'];
        }
//        if (!empty($images)) {
//            $mediaUrl = $this->urlBuilder->getBaseUrl('media');
//            $mediaUrl = trim(str_replace('media', '', $mediaUrl), '/');
//            foreach ($images as &$image) {
//                try {
//                    if (strpos($image['url'], 'base64') !== false) {
//                        $imagePath = $this
//                            ->saveBase64Image($image['url'], '', 'product_labels');
//                        $image['url'] = $imagePath;
//                    }
//                } catch (\Exception $e) {
//                }
//                if (strpos($image['url'], $mediaUrl) === false) {
//                    $image['url'] = str_replace(DS, '/', $mediaUrl . '/' . $image['url']);
//                }
//            }
//        }
        $tab['images'] = $images;
        if (!empty($productInfo['bg_color_active'])) {
            $tab['bg_color_active'] = $productInfo['bg_color_active'];
        }
        $tab = $this->buildUiHeaderData($tab);
        return $tab;
    }

    /**
     * This function builds the ui header data from the tab for size and fonts.
     *
     * @param array $tab
     * @return array
     */
    public function buildUiHeaderData($tab)
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
     * @param \Magento\Catalog\Model\Product $product Product object.
     *
     * @return $this
     */
    public function addValuesForRulesProcessing(\Magento\Catalog\Model\Product $product)
    {
        $productJson = $this->dataObject->getHeaderCurrentJson($product);
        if (!empty($productJson['modules']['crheader'])) {
            $tabInfo = $productJson['modules']['crheader'];

            $texts = $tabInfo['texts'];
            $helper = $this;
            if ($number = count($texts)) {
                $product->setData('number_of_header_text_items', $number);
                $size = 0;
                foreach ($texts as $text) {
                    $width = $helper->fractionalToFloat($text['width_inch']);
                    $height = $helper->fractionalToFloat($text['height_inch']);
                    $size += $width*$height;
                }
                $product->setData('total_size_of_header_text_items', $size);
            }

            $images = $tabInfo['images'];
            if ($number = count($images)) {
                $product->setData('number_of_header_image_items', $number);
                $size = 0;
                foreach ($images as $image) {
                    $width = $helper->fractionalToFloat($image['width_inch']);
                    $height = $helper->fractionalToFloat($image['height_inch']);
                    $size += $width*$height;
                }
                $product->setData('total_size_of_header_image_items', $size);
            }
        }

        return $this;
    }

    //TODO:Have to find the usage of this function in M1 and migrate this to M2.
//    /**
//     * Get calculated tab price
//     *
//     * @param \Magento\Catalog\Model\Product $product Product object.
//     *
//     * @return float
//     */
//    public function getPrice(\Magento\Catalog\Model\Product $product)
//    {
//        /** @var Adg_Customizer_Model_Attribute_Process $ruleModel */
//        $ruleModel = Mage::getModel('adg_customizer/attribute_process');
//        $ruleModel->prepareProductOptions($product)->apply($product);
//        $price = (float) $product->getData('price_of_header_text_items.price');
//        $price += (float) $product->getData('price_of_header_image_items.price');
//
//        return $price;
//    }
}
