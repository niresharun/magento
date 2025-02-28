<?php

namespace Ziffity\CustomFrame\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Registry;
use Magento\Framework\Xml\Parser;
use Magento\Store\Model\StoreManagerInterface;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
use Ziffity\ProductCustomizer\Model\ResourceModel\Entity\Attribute\MultiSelectOptionValueProvider;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Data extends \Ziffity\CustomFrame\Helper\Headers
{

    protected $product = null;

    protected $associatedProducts = null;

    protected $multiselectModel;

    public function __construct(
        Context                          $context,
        ProductOptionRepositoryInterface $productOptionRepository,
        ProductRepositoryInterface       $productRepository,
        Registry                         $registry,
        DirectoryList                    $directoryList,
        DataObject                       $dataObject,
        StoreManagerInterface            $storeManager,
        File                             $file,
        Reader                           $moduleDirReader,
        Parser                           $parser,
        MultiSelectOptionValueProvider   $multiselectModel
    ) {
        $this->multiselectModel = $multiselectModel;
        parent::__construct(
            $context,
            $productOptionRepository,
            $productRepository,
            $registry,
            $directoryList,
            $dataObject,
            $storeManager,
            $file,
            $moduleDirReader,
            $parser
        );
    }

    /**
     * This function checks if the sku has the option header.
     *
     * @param string $sku
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function hasHeader($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
            $customAttr = $product->getAdditionalTabs() ? $this->multiselectModel->getMultiple($product->getAdditionalTabs()) : [];
            return $this->checkInAdditionalTabs($customAttr,"Headers");
        }catch (\Magento\Framework\Exception\NoSuchEntityException $exception){
            return false;
        }
    }

    public function checkInAdditionalTabs($customAttr,$tab)
    {
        if ($customAttr) {
            if (is_string($customAttr)
                && $customAttr == $tab) {
                return true;
            }
            if (is_array($customAttr) &&
                in_array($tab, $customAttr)) {
                return true;
            }
        }
        return false;
    }

    /**
     * This function fetches the size list from the product attribute.
     *
     * @param object $product
     * @param string $attribute
     * @return array
     */
    public function fetchSizeList($product, $attribute)
    {
        $values = [];
        $attributesValuesResult = [];
        $attributeValues = $product->getData($attribute) ? $this->multiselectModel->getMultiple($product->getData($attribute)) : [];
        $tolerance = 1.e-6;
        if ($attributeValues) {
            foreach ($attributeValues as $attributeValue) {
                $hash = $attributeValue.$tolerance;
                if (array_key_exists($hash, $this->floatToFractionalHash)) {
                    $attributesValuesResult[] = [
                        'value'=>$attributeValue,
                        'value_label'=>$this->floatToFractionalHash[$hash]];
                }
            }
            $sortedValues = [];
            foreach ($attributesValuesResult as $attributeValue) {
                $sortedValues[$attributeValue['value']] = $attributeValue;
            }
           // ksort($sortedValues);
            foreach (array_values($sortedValues) as $_value) {
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

    /**
     * This function gets the default size data from the product attribute.
     *
     * @param object $product
     * @param string $type
     * @return false|mixed
     */
    public function getDefaultSize($product, $type)
    {
        $availableWidth = $this->fetchSizeList($product, $type);
        return reset($availableWidth);
    }

    /**
     * Get Product Thickness List
     *
     * @param object|mixed $product
     * @param string $attributeCode
     *
     * @return array
     */
    public function fetchThicknessList($product, $attributeCode = 'graphic_thickness')
    {
        $values = [];
        if ($attributeValues = $this->fetchSizeList($product, $attributeCode)) {
            foreach ($attributeValues as $_value) {
                foreach ($_value['tenth'] as $_tenth) {
                    $intVal = $_value['integer'] ? $_value['integer'] : '';
                    $floatVal = $_tenth ? $_tenth : '';
                    $val = trim($intVal . ' ' . $floatVal);
                    $values[] = $val;
                }
            }
        }
        return array_unique($values);
    }

    /**
     * This function loads the product json for header,opening and label.
     *
     * @param \Magento\Backend\Block\Template $block
     * @return false|string
     * @throws NoSuchEntityException
     */
    public function loadProductJson($block)
    {
        $jsonData = $this->registry->registry('current_product_json');
        if ($jsonData == null) {
            $jsonData = $this->getProductDefaultJson();
        }
        $jsonData["modules"]["mat"]["url"]["sizes"] = $block->getUrl('customframe/index/getMatSizes', ['id'=>4741]);
        $this->registry->register('current_product_json', $jsonData,true);
        return json_encode($jsonData);
    }

    /**
     * This function gets the product json from the registry if exists or else processes and returns.
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getProductDefaultJson()
    {
        $productId = $this->registry->registry('current_product')->getEntityId();
        return $this->getProductJson($productId);
    }

    /**
     * This function returns the product data required for opening,header and label in JSON format.
     *
     * @param string|int $productId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getProductJson($productId)
    {
        $product = $this->productRepository->getById($productId);
        $this->product = $product;
        $widthFirst = $this->getDefaultSize($product, 'dimension_1');//available width
        $heightFirst = $this->getDefaultSize($product, 'dimension_2');//available height
        $thickness = $this->fetchThicknessList($product);
        $adminhtml =  'admin';
        $width = null;
        if ($widthFirst) {
            $tenth = reset($widthFirst['tenth']);
            //  $default_width = $product->getData("size_width");
            //here width is dimension_1 and height is dimension_2
            $default_width = $product->getResource()
                ->getAttribute('dimension_1_default')->getFrontend()->getValue($product);
            if ($default_width) {
                if (strpos($default_width, '/') !==false) {
                    $arr = explode(" ", $default_width);
                    if ($adminhtml == "adminhtml") {
                        $width = ['integer' =>$arr[0], 'tenth' => $tenth];
                    }{
                        $width = ['integer' =>$arr[0], 'tenth' => $arr[1]];
                    }
                } else {
                    $width = ['integer' =>$default_width, 'tenth' => $tenth];
                }
            } else {
                $width = ['integer' => $widthFirst['integer'], 'tenth' => $tenth];
            }
        }
        $height = null;
        if ($heightFirst) {
            $tenth = reset($heightFirst['tenth']);
            $default_height =$product->getResource()
                ->getAttribute('dimension_2_default')->getFrontend()->getValue($product);
            if ($default_height) {
                if (strpos($default_height, '/') !==false) {
                    $arr = explode(" ", $default_height);
                    if ($adminhtml == "adminhtml") {
                        $height = ['integer' =>$arr[0], 'tenth' => $tenth];
                    }{
                        $height = ['integer' =>$arr[0], 'tenth' => $arr[1]];
                    }
                } else {
                    $height = ['integer' =>$default_height, 'tenth' => $tenth];
                }
            } else {
                $height = ['integer' => $heightFirst['integer'], 'tenth' => $tenth];
            }
        }
        $productData = [
            'size'             => [
                'width'  => $width,
                'height' => $height,
            ],
            '_comment_modules' => 'all modules that will be drawn on canvas should to trigger '
                . 'Customizer.addLoadedModule(\'module_name\') after first himself render',
            'test'             => 'update',
            'modules'          => [
                'details' => [
                    'requestURL'    => $this->getDetailsHtmlUrl($product->getId()),
                    'selectionsUrl' => $this->getSelectionHtmlUrl($product->getId()),
                    'priceUrl'      => $this->getPriceHtmlUrl($product->getId()),
                ],
                'size'    => $this->prepareTabSize($product),
            ],
            'timestamp'        => $product->getUpdatedAt()
        ];
        if ($thickness) {
            $productData['size']['thickness'] = reset($thickness);
        }
        $productTabs = $this->getCustomizerTabsByProduct($product);
        $openingSet = false;
        foreach ($productTabs as $code) {
            try {
                if ('laminate_finish' === $code) {
                    $code = 'laminate';
                }
                if (in_array($code, ['Headers','Labels']) && !$openingSet) {
                    if ($tabData = $this->findModelClass('Opening', $productData, $product)) {
                        $moduleCode = !empty($tabData['code']) ? $tabData['code'] : $code;
                        $productData['modules'][$moduleCode] = $tabData;
                        $openingSet = true;
                    }
                }
                if ($code == 'Openings' && $openingSet) {
                    continue;
                }
                if ($tabData = $this->findModelClass($code, $productData, $product)) {
                    $moduleCode = !empty($tabData['code']) ? $tabData['code'] : $code;
                    if ($code == 'Openings'){
                        $openingSet = true;
                    }
                    $productData['modules'][$moduleCode] = $tabData;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        return $productData;
    }

    /**
     * This function finds the model class from the option and executes it.
     *
     * @param string $option
     * @param array|mixed $productData
     * @param object|mixed $product
     * @return array|null
     */
    public function findModelClass($option, $productData, $product)
    {
        switch ($option) {
            case "Headers":
                $this->dataObject->setHeaderCurrentJson($productData);
                return $this->prepareHeaderTab($product);
            case "Labels":
                $this->dataObject->setLabelCurrentJson($productData);
                return $this->prepareLabelTab($product);
            case "Openings":
                $this->dataObject->setOpeningCurrentJson($productData);
                return $this->prepareMatTab($product);
            default:
                return null;
        }
    }

    /**
     * This function checks if the primary products contains the required options.
     *
     * @param object $data
     * @param string|array $optionTitle
     * @return bool
     */
    public function checkPrimaryProductsOptions($data, $optionTitle)
    {
        $additionalTabs =  $data->getData('additional_tabs') ? $this->multiselectModel->getMultiple($data->getData('additional_tabs')) : [];
        if (!is_array($optionTitle)) {
            if ($additionalTabs && !empty($additionalTabs)) {
                if (in_array($optionTitle, $additionalTabs)) {
                    return true;
                }
            }
        }
        if ($additionalTabs && !empty($additionalTabs) && is_array($optionTitle)) {
            foreach ($optionTitle as $value) {
                if (in_array($value, $additionalTabs)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Convert tab/module code.
     *
     * @param string $code Tab/module code.
     *
     * @return string
     */
    protected function _convertModelCode($code)
    {
        $codes = [
            'laminate_interior' => 'laminate_laminate',
            'laminate_exterior' => 'laminate_laminate',
            'laminate'          => 'laminate_laminate',
//            'laminate_finish'   => 'laminate_laminate',
            'top_mat'           => 'mat_mat',
            'bottom_mat'        => 'mat_mat',
            'middle_mat'        => 'mat_mat',
            'mat'               => 'mat_mat',
        ];

        if (!empty($codes[$code])) {
            return $codes[$code];
        }

        return $code;
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
                $fractionalPart = empty($fractionalParts[0]) || empty($fractionalParts[1]) ? '0':
                    $fractionalParts[0]/ $fractionalParts[1];
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
     * Get tab conversion model by code
     *
     * @param string $code Code.
     *
     * @return false|
     */
//    public function getModelByCode($code)
//    {
//        if (!$code) {
//            return false;
//        }
//        $code = $this->_convertModelCode($code);
//
//        $className = Mage::getConfig()->getModelClassName('adg_customizer/convert_' . strtolower($code));
//        if (@!class_exists($className)) {
//            return false;
//        }
//
//        return Mage::getSingleton('adg_customizer/convert_' . strtolower($code));
//    }

    /**
     * Get available tabs list by product
     *
     * @param object $product
     *
     * @return array
     */
    public function getCustomizerTabsByProduct($product)
    {
        $hashKey = 'tabs_' . $product->getId();
        if (!in_array($hashKey, $this->_productTabsHash)) {
            $tabs = [];
            foreach ($this->getAssociatedProducts($product->getSku()) as $option) {
                $tabs[] = $option->getTitle();
            }
            $additionalTabs = $this->getProduct($product->getSku());
            $additionalTabs = $additionalTabs->getAdditionalTabs() ?
                $this->multiselectModel->getMultiple($additionalTabs->getAdditionalTabs()) : [];
            if ($additionalTabs){
                foreach ($additionalTabs as $additionalTab){
                    array_push($tabs,$additionalTab);
                }
            }
            if ($product->getDepthType() !== 'interior_depth' && ($key = array_search('shelves', $tabs)) !== false) {
                unset($tabs[$key]);
            }
            $this->_productTabsHash[$hashKey] = $tabs;
        }
        return $this->_productTabsHash[$hashKey];
    }

    public function getAssociatedProducts($sku)
    {
        if ($this->associatedProducts){
            return $this->associatedProducts;
        }
        $this->associatedProducts = $this->productOptionRepository->getList($sku);
        return $this->associatedProducts;
    }

    public function getProduct($product)
    {
        if ($this->product){
            return $this->product;
        }
        $this->product = $this->productRepository->get($product->getSku());
        return $this->product;
    }

    /**
     * Prepare Size Tab Json
     *
     * @param object $product
     *
     * @return array
     */
    public function prepareTabSize($product)
    {
        $urlHelper = $this;
        $attribute = $product->getResource()->getAttribute('size_type');
        $attributeNickname = $product->getResource()->getAttribute('nickname_sizes');
        $sizeType = null;
        $sizeNickname = '';
        if ($attribute->getId()) {
            $sizeType = strtolower($attribute->getFrontend()->getValue($product));
            $sizeNickname = ucfirst($sizeType);
        }
        if ($attributeNickname->getId() && ($value = $attributeNickname->getFrontend()->getValue($product))) {
            $sizeNickname = $value;
        }
        return [
            'url'      => [
                'html' => $urlHelper->getSizeHtmlUrl($product->getId()),
                'data' => $urlHelper->getSizeDataUrl($product->getId()),
            ],
            '_comment' => 'type should to be \'frame\' or \'graphic\'',
            'type'     => $sizeType,
            'type_name'=> $sizeNickname
        ];
    }

    /**
     * Size Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getSizeHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl('customizer/tabs/sizeHtml', ['id' => $productId]);
    }

    /**
     * Size Data Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getSizeDataUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/sizeData',
            ['id' => $productId]
        );
    }

    /**
     * Product Details Html Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getDetailsHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/getDetailsHtml',
            ['id' => $productId]
        );
    }

    /**
     * Product Get Data Json  Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getPriceHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/getPriceData',
            ['id' => $productId]
        );
    }

    /**
     * Product Get Data Json  Url
     *
     * @param integer $productId Product id.
     *
     * @return string
     */
    public function getSelectionHtmlUrl($productId)
    {
        return $this->_urlBuilder->getUrl(
            'customizer/tabs/getSelections',
            ['id' => $productId]
        );
    }

    /**
     * This function checks if the sku has opening as product title option or not.
     *
     * @param string $sku
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function hasOpening($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
            $customAttr = $product->getAdditionalTabs() ? $this->multiselectModel->getMultiple($product->getAdditionalTabs()) : [];
            return $this->checkInAdditionalTabs($customAttr,"Openings");
        }catch (\Magento\Framework\Exception\NoSuchEntityException $exception){
            return false;
        }
    }

    /**
     * This function checks if the sku has label section in the product title or not.
     *
     * @param string $sku
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function hasLabel($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
            $customAttr = $product->getAdditionalTabs() ? $this->multiselectModel->getMultiple($product->getAdditionalTabs()) : [];
            return $this->checkInAdditionalTabs($customAttr,"Labels");
        }catch (\Magento\Framework\Exception\NoSuchEntityException $exception){
            return false;
        }
    }

    /**
     * Retrieve all attribute options
     *
     * @return array
     */
    public function jsonFractionalValues()
    {
        $filePath = $this->moduleDirReader->getModuleDir('etc', 'Ziffity_CustomFrame')
            . '/FractionalValues.json';
        $fileContents = $this->file->read($filePath);
        return json_decode($fileContents, true);
    }

    /**
     * This function calculates the size data attribute to integer and decimals.
     *
     * @return array
     */
    public function getSizes()
    {
        $tenthSizes = [
            $this->formatFloatToFractional(0.25),
            $this->formatFloatToFractional(0.375),
            $this->formatFloatToFractional(0.5),
            $this->formatFloatToFractional(0.625),
            $this->formatFloatToFractional(0.75),
            $this->formatFloatToFractional(0.875),
        ];

        $sizes = [
            'top'    => [
                'integer' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                'tenth'   => $tenthSizes,
            ],
            'reveal' => $tenthSizes,
        ];

        $sizes['reveal'] = [];

        return $sizes;
    }

    /**
     * Convert float value to fractional string (e.g. 0.125 => 1/8).
     *
     * @param float $n         Float value.
     * @param float $tolerance Tolerance.
     *
     * @return string
     */
    public function formatFloatToFractional($n, $tolerance = 1.e-6)
    {
        $value = $this->floatToFractional($n, $tolerance);
        if ($value['decimal']) {
            if ($value['fractional']['top'] && $value['fractional']['bottom']) {
                return $value['decimal'] . ' ' . $value['fractional']['top'] . '/' . $value['fractional']['bottom'];
            }
            return $value['decimal'];
        }
        if ($value['fractional']['bottom'] > 0) {
            return $value['fractional']['top'] . '/' . $value['fractional']['bottom'];
        }
        return $value['fractional']['top'];
    }

    /**
     * Convert float value to fractional string (e.g. 0.125 => 1/8).
     *
     * @param float $n         Float value.
     * @param float $tolerance Tolerance.
     *
     * @return array
     */
    public function floatToFractional($n, $tolerance = 1.e-6)
    {
        if (!$n) {
            return [
                'decimal'    => 0,
                'fractional' => [
                    'top'     => 0,
                    'bottom'  => 0,
                    'decimal' => 0,
                ],
            ];
        }
        $hashId = $n . $tolerance;
        if (array_key_exists($hashId, $this->floatToFractionalHash)) {
            return $this->floatToFractionalHash[$n . $tolerance];
        }
        $decimalPartOne = 1;
        $decimalPartTwo = 0;
        $fractionalPartOne = 0;
        $fractionalPartTwo = 1;
        $b = 1 / $n;
        do {
            $b = 1 / $b;
            $a = floor($b);
            $aux = $decimalPartOne;
            $decimalPartOne = $a * $decimalPartOne + $decimalPartTwo;
            $decimalPartTwo = $aux;
            $aux = $fractionalPartOne;
            $fractionalPartOne = $a * $fractionalPartOne + $fractionalPartTwo;
            $fractionalPartTwo = $aux;
            $b -= $a;
        } while (abs($n - $decimalPartOne / $fractionalPartOne) > $n * $tolerance);

        $converted = [
            'decimal'    => 0,
            'fractional' => [
                'top'     => $decimalPartOne,
                'bottom'  => $fractionalPartOne,
                'decimal' => $decimalPartOne / $fractionalPartOne,
            ],
        ];
        if ($decimalPart = (int) ($decimalPartOne / $fractionalPartOne)) {
            $converted = [
                'decimal'    => $decimalPart,
                'fractional' => [
                    'top'     => 0,
                    'bottom'  => 0,
                    'decimal' => 0,
                ],
            ];
            if ($fractionalPart = $decimalPartOne % $fractionalPartOne) {
                $converted = [
                    'decimal'    => $decimalPart,
                    'fractional' => [
                        'top'     => $fractionalPart,
                        'bottom'  => $fractionalPartOne,
                        'decimal' => $fractionalPart / $fractionalPartOne,
                    ],
                ];
            }
        }
        $this->floatToFractionalHash[$hashId] = $converted;
        return $converted;
    }

    /**
     * Retrieve config value by path and scope.
     *
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param string $scopeType The scope to use to determine config value, e.g., 'store' or 'default'
     * @param null|int|string $scopeCode
     * @return mixed
     */
    public function getConfigValue($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->scopeConfig->getValue($path,$scopeType,$scopeCode);
    }

    /**
     * @param $mixedFractionStr
     * @return array
     */
    public function mixedFractionToNumber($mixedFractionStr)
    {
        $converted = [];
        if($mixedFractionStr) {
            $mixedFractionStr = explode(' ', $mixedFractionStr);
            $decimalPart = (int)$mixedFractionStr[0];
            $numerator = 0;
            $denominator = 0;
            $decimal = 0;
            if(isset($mixedFractionStr[1])) {
                // Extract the numerator and denominator from the fraction part
                list($numerator, $denominator) = explode('/', $mixedFractionStr[1]);
                $numerator = (int)$numerator;
                $denominator = (int)$denominator;
                $decimal = $numerator / $denominator;
            }
            $converted = [
                'decimal'    => $decimalPart,
                'fractional' => [
                    'top'     => $numerator,
                    'bottom'  => $denominator,
                    'decimal' => $decimal,
                ],
            ];
        }
        return $converted;
    }
}
