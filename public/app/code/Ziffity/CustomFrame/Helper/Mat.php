<?php

namespace Ziffity\CustomFrame\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Xml\Parser;
/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Mat extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var File
     */
    public $file;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var DirectoryList
     */
    public $directory;

    /**
     * @var UrlInterface
     */
    public $urlBuilder;

    /**
     * @var DataObject
     */
    public $dataObject;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var ProductRepositoryInterface
     */
    public $productRepository;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleDirReader;

    /**
     * @var ProductOptionRepositoryInterface
     */
    public $productOptionRepository;

    /**
     * @var array
     */
    protected $_productTabsHash = [];

    /**
     * @var array
     */
    protected $floatToFractionalHash = [];

    /**
     * @param Context $context
     * @param ProductOptionRepositoryInterface $productOptionRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Registry $registry
     * @param DirectoryList $directoryList
     * @param DataObject $dataObject
     * @param StoreManagerInterface $storeManager
     * @param File $file
     * @param Reader $moduleDirReader
     * @param Parser $parser
     */
    public function __construct(
        Context                          $context,
        ProductOptionRepositoryInterface $productOptionRepository,
        ProductRepositoryInterface       $productRepository,
        Registry                         $registry,
        DirectoryList                    $directoryList,
        DataObject                       $dataObject,
        StoreManagerInterface            $storeManager,
        File                             $file,
        Reader $moduleDirReader,
        Parser $parser
    ) {
        $this->productOptionRepository = $productOptionRepository;
        $this->productRepository = $productRepository;
        $this->urlBuilder = $this->_urlBuilder;
        $this->registry = $registry;
        $this->directory = $directoryList;
        $this->dataObject = $dataObject;
        $this->storeManager = $storeManager;
        $this->file = $file;
        $this->moduleDirReader = $moduleDirReader;
        $this->parser = $parser;
        parent::__construct($context);
    }

    /**
     * Prepare mat tab json object
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $matProductInfo
     *
     * @return array|null
     */
    public function prepareMatTab(\Magento\Catalog\Model\Product $product, array $matProductInfo = null)
    {
        $matboard = [];
        $activeItems = [];
//        if ($this->isMatTabEnabled($product, 'top')) {
//            $matboard['top'] = $this->getMatTopDataUrl($product->getId());
//            $activeItems['top'] = $this->getActiveMat($product, 'top', $matProductInfo);
//        }
//        if ($this->isMatTabEnabled($product, 'middle')) {
//            $matboard['middle'] = $this->getMatMiddleDataUrl($product->getId());
//            $activeItems['middle'] = $this->getActiveMat($product, 'middle', $matProductInfo);
//        }
//        if ($this->isMatTabEnabled($product, 'bottom')) {
//            $matboard['bottom'] = $this->getMatBottomDataUrl($product->getId());
//            $activeItems['bottom'] = $this->getActiveMat($product, 'bottom', $matProductInfo);
//        }
        $tabData = [
            'code'         => 'mat',
            'for_drawing'  => '1',
            'order'        => 90,
            'url'          => [
                'html'     => $this->getMatHtmlUrl($product->getId()),
                'sizes'    => $this->getMatSizeslUrl($product->getId()),
                'popup'    => $this->getMatListPopupUrl($product->getId()),
                'matboard' => $matboard,
            ],
            'sizes'        => $this->getCurrentSizes($product, $matProductInfo),
            'active_items' => $activeItems,
            'openings'     => false,
        ];
//        if ($this->isOpeningTabEnabled($product)) {
//            $tabData['openings'] = $this->prepareOpening($product, $matProductInfo);
//        }
        $tabData['openings'] = $this->prepareOpening($product, $matProductInfo);
        $matboardOverlap = $this->product->getResource()
            ->getAttributeRawValue(
                $product->getId(),
                'matboard_overlap',
                $this->storeManager->getStore()->getId()
            );
        $tabData['overlap'] =
            $matboardOverlap ? $this->formatFloatToFractional($matboardOverlap) : 0;
        $openingSizes = $this->product->getResource()
            ->getAttributeRawValue(
                $product->getId(),
                'opening_size',
                $this->storeManager->getStore()->getId()
            );
        if ($openingSizes) {
            $sizes = json_decode($openingSizes, true);
            $tabData['sizes_lock'] = !empty($sizes['sizes_lock']) ? $sizes['sizes_lock'] : '';
        }
        return $tabData;
    }

    /**
     * Check if mat tab is enabled.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $tabCode
     *
     * @return boolean
     */
    protected function isMatTabEnabled($product, $tabCode)
    {
        $tabCode .= '_mat';
        $productTabs = $this->getCustomizerTabsByProduct($product);

        return (bool) in_array($tabCode, $productTabs);
    }

    /**
     * Check if opening tab is enabled.
     *
     * @param \Magento\Catalog\Model\Product $product Product object.
     *
     * @return boolean
     */
    protected function isOpeningTabEnabled($product)
    {
        $tabCode = 'Opening';
        $productTabs = $this->getCustomizerTabsByProduct($product);

        return (bool) in_array($tabCode, $productTabs);
    }

    /**
     * Retrieve current sizes.
     *
     * @param \Magento\Catalog\Model\Product|null $product
     * @param array $matProductInfo
     *
     * @return array
     */
    public function getCurrentSizes(\Magento\Catalog\Model\Product $product = null, $matProductInfo = null)
    {
        $sizes = [
            'top'    => [
                'integer' => '1',
                'tenth'   => '1/2',
            ],
            'reveal' => '1/2',
        ];

        $openingSizes = $product->getResource()
            ->getAttributeRawValue(
                $product->getId(),
                'opening_size',
                $this->storeManager->getStore()->getId()
            );

        if ($openingSizes) {
            $sizes = json_decode($openingSizes, true);
            unset($sizes['sizes_lock']);
        }

        if (empty($matProductInfo)) {
            $currentJson = $this->dataObject->getMatCurrentJson($product);
            $matProductInfo = !empty($currentJson['modules']['mat']) ? $currentJson['modules']['mat'] : [];
        }

        if (!empty($matProductInfo['sizes'])) {
            $sizes = $matProductInfo['sizes'];
        }

        if (!$this->isMatTabEnabled($product, 'middle')
            && !$this->isMatTabEnabled($product, 'bottom')
        ) {
            unset($sizes['reveal']);
        }

        return $sizes;
    }

    /**
     * Retrieve active Mat info.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $matPosition
     * @param array $moduleInfo
     *
     * @return array
     */
    public function getActiveMat(\Magento\Catalog\Model\Product $product, $matPosition, array $moduleInfo = null)
    {
        //TODO:When the product option have been saved with any type of mat
        //TODO:like bottom_mat or top_mat or middle_mat then this function is
        //TODO:used and the mat associated product's attribute data have been
        //TODO:used for now keeping it commented as those functionality have not been implemented yet.
        $activeId = null;
//        switch ($matPosition) {
//            case 'top':
//                $linkTypeId = Adg_Customizer_Model_Product_Type_Template::LINK_TYPE_TOP_MAT;
//                break;
//            case 'middle':
//                $linkTypeId = Adg_Customizer_Model_Product_Type_Template::LINK_TYPE_MIDDLE_MAT;
//                break;
//            case 'bottom':
//                $linkTypeId = Adg_Customizer_Model_Product_Type_Template::LINK_TYPE_BOTTOM_MAT;
//                break;
//            default:
//                $linkTypeId = 0;
//        }
//
//        if (!empty($moduleInfo['active_items'][$matPosition]['id'])) {
//            $activeId = $moduleInfo['active_items'][$matPosition]['id'];
//
//            $availableList = $this->getAvailableList($product, $linkTypeId);
//            /*$productIds = $product->getTypeInstance()
//                ->getUsedProductIds($product, $linkTypeId);*/
//
//            $productIds = $availableList->getAllIds();
//
//            if (!in_array($activeId, $productIds)) {
//                $activeId = null;
//                $this->addError('mat');
//            }
//        }
//
//        if (!$activeId) {
//            $activeId = $this->getProductFirstList($product, $linkTypeId);
//        }
//
//        $activeProduct = Mage::getModel('catalog/product')->load($activeId);
//
//        if (!$activeProduct->getId()) {
//            Mage::getSingleton('adg_customizer/convert')->setData('no_options', true)->addError($this->getLinkName());

            return null;
        //        return $this->convertProduct($activeProduct, true, true);
    }

    /**
     * Get Model Link Name
     *
     * @return integer
     */
    public function getLinkName()
    {
        return 'mat';
    }

    /**
     * Fetch information from mat product
     *
     * @param \Magento\Catalog\Model\Product $_product
     * @param boolean $activeState
     * @param boolean $skipPrice
     * @SuppressWarnings("BooleanArgumentFlag")
     *
     * @return array|null
     */
    public function convertProduct(\Magento\Catalog\Model\Product $_product, $activeState = false, $skipPrice = false)
    {
        //TODO:Copied from M1 since these functions are not used here in M2 have to analyse it's usages.
//        try {
//            $width = $this->floatToFractional($_product->getLayerWidth());
//            $height = $this->floatToFractional($_product->getLayerHeight());
//            try {
//                $imgThumb = (string) $this->product
//                ->init($_product, 'img_thumb')->keepFrame(false)->resize(75);
//            } catch (\Exception $exception) {
//                $imgThumb = '';
//            }
//            $type = 'pattern';
//            $pattern = $_product->getPatternMat();
//            $imgDraw = Mage::getDesign()->getSkinUrl('images/customizer/mats/' . $pattern . '.jpg');
//            if ($pattern == 'paper') {
//                $imgDraw = '';
//            }
//            if ($_product->getImgLayer()) {
//                $imgDraw = (string) Mage::helper('catalog/image')->init($_product, 'img_layer');
//                $type = 'image';
//            }
//            $resultJson = [
//            'id'        => $_product->getId(),
//            'active'    => $activeState,
//            'name'      => $_product->getName(),
//            'color'     => '#' . $_product->getColorLayer(),
//            'code'      => $_product->getCode() ? $_product->getCode() : $_product->getSku(),
//            'supplier'  => $_product->getResource()->getAttribute('supplier')
//                ->getFrontend()->getValue($_product),
//            'price'     => $skipPrice ? '' : $this->formatPrice($_product->getPrice()),
//            'img_thumb' => $imgThumb,
//            'img_draw'  => [
//                'type'   => $type, //"pattern" or "image"
//                'src'    => $imgDraw,
//                'width'  => [
//                    'integer' => $width['decimal'],
//                    'tenth'   => (!$width['decimal']['top']) ? 0 : ($width['decimal']['top']
//                        . '/' . $width['decimal']['bottom']),
//                ],
//                'height' => [
//                    'integer' => $height['decimal'],
//                    'tenth'   => (!$height['decimal']['top']) ? 0 : ($height['decimal']['top']
//                        . '/' . $height['decimal']['bottom']),
//                ],
//            ],
//            'url'       => ['details' => Mage::helper('adg_customizer/url')->getMatPopupUrl($_product->getId())],
//            ];
//            return $resultJson;
//        } catch (\Exception $e) {
//    //            Mage::logException($e);
//            //TODO:Have to log errors.
//            $foundError = true;
//        }
        return null;
    }

    /**
     * Prepare mat opening
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $matProductInfo
     *
     * @return array|null
     */
    public function prepareOpening(\Magento\Catalog\Model\Product $product, array $matProductInfo = null)
    {
        $openings = $product->getOpeningData();
        if ($openings === null) {
            $openings[] = ['shape' => 'rectangle'];
            $openings = json_encode($openings);
        }
        if (!empty($openings)) {
            $openings = json_decode($openings, true);
            if (empty($openings)) {
                $openings[] = ['shape' => 'rectangle'];
            }
        }
        if (!empty($matProductInfo['openings'])) {
            $openings = $matProductInfo['openings']['list'];
            $openingsCount = count($openings);
            for ($i = 0; $i < $openingsCount; $i++) {
                if (isset($openings[$i]['img']['url']) && strpos($openings[$i]['img']['url'], 'data:image') === 0) {
                    $openings[$i]['img']['url']
                    = $this->urlBuilder->getBaseUrl()
                    . $this->saveBase64Image($openings[$i]['img']['url'], 'Opening', '', '');
                }
            }
        }
        $result = ['list' => $openings, 'type' => 'single'];
        if (count($openings) >= 1 && !empty($openings[0]['position'])) {
            $result['type'] = 'multiple';
        }
        $result = $this->addImageUrl($result);
        return $result;
    }

    /**
     * This functions get the url for the image from the filename to access it from the browser.
     *
     * @param array $result
     * @return array
     * @throws NoSuchEntityException
     */
    public function addImageUrl($result)
    {
        if (isset($result['list'])) {
            foreach ($result['list'] as $key => $data) {
                if (isset($data['img']) && strpos($data['img']['url'], 'http') !== 0) {
                    $result['list'][$key]['img']['url'] =
                    $this->getMediaUrl().'catalog/product/opening/'.$data['img']['url'];
                }
            }
        }
        return $result;
    }

    /**
     * Get media url
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMediaUrl()
    {
        //Get the url for the image.
        $mediaUrl = $this->storeManager->getStore()
        ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl;
    }

    /**
     * Get active ids
     *
     * @param \Magento\Catalog\Model\Product $product Product object.
     *
     * @return array
     */
    public function getActiveIds(\Magento\Catalog\Model\Product $product)
    {
        //TODO:This function has been copied from the M1 have to analyse if it is usable in M2.
//        $relation = Mage::registry('current_product_relation');
//        if (!empty($relation)) {
//            $relation = str_replace('_mat', '', $relation);
//        }
//        $currentJson = Mage::getSingleton('customer/session')->getData('customizer_product_' . $product->getId());
//        $activeIds = [];
//        if (!empty($currentJson['modules']['mat']['active_items'])) {
//            foreach ($currentJson['modules']['mat']['active_items'] as $key => $activeItem) {
//                if (!empty($relation) && $key == $relation) {
//                    $activeIds[] = $activeItem['id'];
//                }
//            }
//
//            return array_unique($activeIds);
//        }
        return [];
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
        //TODO:This function has been copied from M1 have to analyse if it is usable in M2.
//        $currentJson = Mage::getSingleton('customer/session')->getData('customizer_product_' . $product->getId());
//        if (!empty($currentJson['modules']['mat']['active_items'])) {
//            foreach ($currentJson['modules']['mat']['active_items'] as $key => $activeItem) {
//                $product->addData(
//                    [
//                    $key . '_' . $this->getLinkName() => $activeItem['id'],
//                    ]
//                );
//            }
//        }
        return $this;
    }

    /**
     * Retrieve Mat Sizes.
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
        $product = $this->_getProduct();
        if ($product !== false
        && !$this->isMatTabEnabled($product, 'middle')
        && !$this->isMatTabEnabled($product, 'bottom')
        ) {
            $sizes['reveal'] = [];
        }
        return $sizes;
    }

    /**
     * Get json for product list
     *
     * @param mixed $productList
     *
     * @return string|array
     */
    public function prepareProductList($productList)
    {
        $value = [];
        //TODO:This function has been copied from M1 have to analyse if it usable in M2.
//        if ($productList->getSize()) {
//            foreach ($productList as $_product) {
//                try {
//                    if (($attributeSetName = $this->getProductKey($_product))
//                    && $model = $this->getModelByCode(strtolower($attributeSetName))
//                    ) {
//                        $activeIds = $model->getActiveIds(Mage::registry('current_product'));
//                        $value[] = $model->convertProduct($_product, in_array($_product->getId(), $activeIds));
//                    }
//                } catch (Exception $e) {
//                    continue;
//                }
//            }
//        }
        return $value;
    }

    /**
     * This function gets the available list from the product object.
     *
     * @param Product $product
     * @param mixed $linkType
     * @return null
     */
    public function getAvailableList(\Magento\Catalog\Model\Product $product, $linkType)
    {
        //TODO:This function was being used in M1 have to analyse if it is usable in M2.
        /** @var $frameModel Adg_Customizer_Model_Convert_Frame */
//        $frameModel = Mage::getModel('adg_customizer/convert_frame');
//        $frameOverlap = $frameModel->getFrameOverlap();
//        $frameWidth = $frameModel->getWidth() + $frameOverlap * 2;
//        $frameHeight = $frameModel->getHeight() + $frameOverlap * 2;
//        $max = max($frameWidth, $frameHeight);
//        $min = min($frameWidth, $frameHeight);
//        /** @var Adg_Customizer_Model_Resource_Product_Type_Template_Product_Collection $collection */
//        $collection = clone($product->getTypeInstance()->getUsedProductCollection($product, $linkType));
//        $collection->addAttributeToSelect(['mat_width', 'mat_height']);
//        $collection->addAttributeToFilter('mat_width', ['gt' => 0]);
//        $collection->addAttributeToFilter('mat_height', ['gt' => 0]);
//        $collection->getSelect()->where(
//            'GREATEST(at_mat_width.value, at_mat_height.value) >= ' . $max . '
//            AND LEAST(at_mat_width.value, at_mat_height.value) >= ' . $min
//        );
//        return $collection;
        return null;
    }

    /**
     * Get first available product in relation
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $productRelation
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProductFirstList(\Magento\Catalog\Model\Product $product, $productRelation)
    {
        $productList = $this->getAvailableList($product, $productRelation);
        if (($id = $this->_getActiveMatId($productList, $productRelation)) && !empty($id)) {
            return $id;
        }
        $productIds = $productList->getAllIds();
        $default = null;
        $first = null;
        if ($productIds) {
            foreach ($productList as $item) {
                if ($item->getIsDefault()) {
                    $default = $item->getId();
                    break;
                }
                if (null === $first) {
                    $first = $item->getId();
                }
            }
        }
        if ($default) {
            return $default;
        }
        return $first;
    }

    /**
     * This function gets the active mat ids from the product being passed in param.
     *
     * @param mixed $productList
     * @param mixed $productRelation
     * @return mixed
     */
    protected function _getActiveMatId($productList, $productRelation)
    {
        //TODO: Copied from M1 have to find if it is usable in M2.
//        $select = clone $productList->getSelect();
//        $adapter = $productList->getResource()->getReadConnection();
//        $select->reset(Zend_Db_Select::GROUP);
//        $select->where('is_default = 1');
//        $select->where('link_type = ?', $productRelation);
//        $value = $adapter->fetchOne($select);
//        if (!empty($value)) {
//            return $value;
//        }
        return false;
    }

    /**
     * This function get the product model object.
     *
     * @return false|Product
     */
    protected function _getProduct()
    {
        if ($this->product && $this->product->getId()) {
            return $this->product;
        }
        return false;
    }

    /**
     * Fetch tab description
     *
     * @param \Magento\Catalog\Model\Product $product Product model.
     *
     * @return string[][]
     */
    public function fetchTabDescription(\Magento\Catalog\Model\Product $product)
    {
        $json = $this->dataObject->getCurrentTabJson($product);
        $tabInfo = [];
        if (!empty($json['modules']['mat']['active_items']['top']['id'])) {
            $item = $json['modules']['mat']['active_items']['top'];
            $tabInfo[] = ['label' => __('Top'), 'value' => $item['name']];
        }
        if (!empty($json['modules']['mat']['active_items']['middle']['id'])) {
            $item = $json['modules']['mat']['active_items']['middle'];
            $tabInfo[] = ['label' => __('Middle'), 'value' => $item['name']];
        }
        if (!empty($json['modules']['mat']['active_items']['bottom']['id'])) {
            $item = $json['modules']['mat']['active_items']['bottom'];
            $tabInfo[] = ['label' => __('Bottom'), 'value' => $item['name']];
        }
        return $tabInfo;
    }

    /**
     * Fetch tab description
     *
     * @param \Magento\Catalog\Model\Product $product Product model.
     *
     * @return string[][]
     */
    public function fetchTabDescriptionOrder(\Magento\Catalog\Model\Product $product)
    {
        $json = $this->dataObject->getMatCurrentJson($product);
        $tabInfo = [];
        if (!empty($json['modules']['mat']['active_items']['top']['id'])) {
            $item = $json['modules']['mat']['active_items']['top'];
            $tabInfo[] = ['label' => __('Mat: Top'), 'value' => $item['name']];
            $tabInfo[] = ['label' => __('Mat: Top Code'), 'value' => $item['code']];
            $tabInfo[] = ['label' => __('Mat: Top Vendor'), 'value' => $item['supplier']];
        }
        if (!empty($json['modules']['mat']['active_items']['middle']['id'])) {
            $item = $json['modules']['mat']['active_items']['middle'];
            $tabInfo[] = ['label' => __('Mat: Middle'), 'value' => $item['name']];
            $tabInfo[] = ['label' => __('Mat: Middle Code'), 'value' => $item['code']];
            $tabInfo[] = ['label' => __('Mat: Middle Vendor'), 'value' => $item['supplier']];
        }
        if (!empty($json['modules']['mat']['active_items']['bottom']['id'])) {
            $item = $json['modules']['mat']['active_items']['bottom'];
            $tabInfo[] = ['label' => __('Mat: Bottom'), 'value' => $item['name']];
            $tabInfo[] = ['label' => __('Mat: Bottom Code'), 'value' => $item['code']];
            $tabInfo[] = ['label' => __('Mat: Bottom Vendor'), 'value' => $item['supplier']];
        }
        return $tabInfo;
    }
}
