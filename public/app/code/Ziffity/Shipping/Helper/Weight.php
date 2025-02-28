<?php

namespace Ziffity\Shipping\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Registry;
use Magento\Framework\Xml\Parser;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
use Ziffity\CustomFrame\Model\Product\Type;
use Ziffity\ProductCustomizer\Model\Components\Measurements\FrameSize;
use Ziffity\ProductCustomizer\Model\ResourceModel\Entity\Attribute\MultiSelectOptionValueProvider;

class Weight extends \Ziffity\CustomFrame\Helper\Data
{

    public $dataObject;
    /**
     * @var ProductRepositoryInterface
     */
    public $productRepository;
    /**
     * Product json.
     *
     * @var array
     */
    protected $json = [];
    /**
     * Size helper.
     *
     * @var FrameSize
     */
    protected $sizeHelper;
    /**
     * Product.
     *
     * @var Product
     */
    protected $product;
    /**
     * @var string[]
     */
    protected $_calculateHash;

    /**
     * @var MultiSelectOptionValueProvider
     */
    protected $multiSelectModel;

    public function __construct(
        Context $context,
        ProductOptionRepositoryInterface $productOptionRepository,
        ProductRepositoryInterface $productRepository,
        Registry $registry,
        DirectoryList $directoryList,
        DataObject $dataObject,
        StoreManagerInterface $storeManager,
        File $file,
        Reader $moduleDirReader,
        Parser $parser,
        MultiSelectOptionValueProvider $multiSelectModel,
        FrameSize $frameSize
    )
    {
        $this->sizeHelper = $frameSize;
        $this->productRepository = $productRepository;
        $this->dataObject = $dataObject;
        parent::__construct($context, $productOptionRepository,
            $productRepository, $registry, $directoryList, $dataObject,
            $storeManager, $file, $moduleDirReader, $parser, $multiSelectModel);
    }

    /**
     * @param Item|array|object $item
     *
     * @return float|int
     */
    public function calculateQuoteItemWeight($item)
    {
        if (!empty($this->_calculateHash[$item->getId()])) {
            $this->_calculateHash[$item->getId()];
        }
        $this->product = $item->getProduct();
        $productTypeId = $this->product->getTypeId();
        if ($productTypeId == Type::TYPE_CODE) {
            $this->_calculateHash[$item->getId()] = $this->calculateTemplateItemWeight($item);
            return $this->_calculateHash[$item->getId()];
        }
        $this->_calculateHash[$item->getId()] = $this->calculateSimpleItemWeight();
        return $this->_calculateHash[$item->getId()];
    }

    protected function calculateTemplateItemWeight($item)
    {
        $itemWeight = 0;
        $this->json = json_decode($item->getAdditionalData(), true);
        $itemWeight += $this->getFrameWeight();
        $itemWeight += $this->getSquareWeightForModule('fabric');
        $itemWeight += $this->getMatsWeight();
        $itemWeight += $this->getSquareWeightForModule('glass');
        $itemWeight += $this->getSquareWeightForModule('chalkboards');
        $itemWeight += $this->getSquareWeightForModule('corkboards');
        $itemWeight += $this->getSquareWeightForModule('dryeraseboard');
        $itemWeight += $this->getSquareWeightForModule('letterboard');
        $itemWeight += $this->getWeightForModule('postfinish');
        $itemWeight += $this->getBackingBoardWeight();
        return round($itemWeight, 2);
    }

    /**
     * Get Frame weight.
     *
     * @return float
     */
    public function getFrameWeight()
    {
        $frameWeight = 0;
        $frameId = (int)$this->json['frame']['active_item']['id'];
        $frameProduct = $this->getProductById($frameId);
        if ($frameProduct !== null) {
            $weight = $frameProduct->getWeight();
            $overallFrameWidth = $this->getOverallFrameWidth();
            $overallFrameHeight = $this->getOverallFrameHeight();
            $frameWeight = (($overallFrameHeight * 2) + ($overallFrameWidth * 2)) * $weight;
        }
        return (float)$frameWeight;
    }

    /**
     * Get Frame Product.
     *
     * @param integer|string $id
     *
     * @return null|ProductInterface
     */
    protected function getProductById($id)
    {
        try {
            return $this->productRepository->getById($id);
        } catch (NoSuchEntityException $exception) {
            return null;
        }
    }

    /**
     * @return float
     */
    public function getOverallFrameWidth()
    {
        $innerFrameWidth = $this->sizeHelper->getInnerFrameWidth($this->json);
        $layerHeight = $this->getLayerHeight() * 2;
        return $innerFrameWidth + $layerHeight;
    }

    /**
     * @return float
     */
    protected function getLayerHeight()
    {
        $layerHeight = $this->json['frame']['active_item']['img_draw']['height']['integer']
            . ' '
            . $this->json['frame']['active_item']['img_draw']['height']['tenth'];
        $layerHeight = $this->fractionalToFloat($layerHeight);
        return (float)$layerHeight;
    }

    /**
     * @return float
     */
    public function getOverallFrameHeight()
    {
        $innerFrameHeight = $this->sizeHelper->getInnerFrameHeight($this->json);
        $layerHeight = $this->getLayerHeight() * 2;
        return $innerFrameHeight + $layerHeight;
    }

    /**
     * Calculate weight based on item area.
     *
     * @param string $moduleName
     *
     * @return float
     */
    public function getSquareWeightForModule($moduleName)
    {
        $itemWeight = 0;
        if (!empty($this->json[$moduleName]['active_item'])) {
            $backOfMouldingWidth = $this->sizeHelper->getBackOfMouldingWidth($this->json);
            $overallFrameWidth = $this->getOverallFrameWidth();
            $overallFrameHeight = $this->getOverallFrameHeight();
            $overallFrameWidth = $overallFrameWidth - ($backOfMouldingWidth * 2);
            $overallFrameHeight = $overallFrameHeight - ($backOfMouldingWidth * 2);
            if ($moduleName == 'glass') {
                $overallFrameWidth = $overallFrameWidth - 0.0625;
                $overallFrameHeight = $overallFrameHeight - 0.0625;
            }
            $area = (float)$overallFrameWidth * (float)$overallFrameHeight;
            $product = $this->getProductById($this->json[$moduleName]['active_item']['id']);
            $weight = 0;
            if ($product) {
                $weight = $product->getWeight();
            }
            $itemWeight = $weight * $area;
        }

        return (float)$itemWeight;
    }

    public function getMatsWeight()
    {
        $matsWeight = 0;
        if (!empty($this->json['mat']['active_items'])) {
            $backOfMouldingWidth = $this->sizeHelper->getBackOfMouldingWidth($this->json);
            $overallFrameWidth = $this->getOverallFrameWidth();
            $overallFrameHeight = $this->getOverallFrameHeight();
            $overallFrameWidth = $overallFrameWidth - ($backOfMouldingWidth * 2);
            $overallFrameHeight = $overallFrameHeight - ($backOfMouldingWidth * 2);
            $area = (float)$overallFrameWidth * (float)$overallFrameHeight;
            $weights = [];
            foreach ($this->json['mat']['active_items'] as $activeItem) {
                $product = $this->getProductById($activeItem['id']);
                if ($product) {
                    $weights[] = $product->getWeight();
                }
            }
            foreach ($weights as $weight) {
                $matsWeight += $weight * $area;
            }
        }
        return (float)$matsWeight;
    }

    public function getWeightForModule($moduleName)
    {
        $itemWeight = 0;
        if (!empty($this->json[$moduleName]['active_item'])) {
            $product = $this->getProductById($this->json[$moduleName]['active_item']['id']);
            if ($product) {
                $itemWeight = $product->getWeight();
            }
        }
        return (float)$itemWeight;
    }

    /**
     * @return float
     */
    public function getBackingBoardWeight()
    {
        $itemWeight = 0;
        if (!empty($this->json['backingboard']['active_item'])) {
            $product = $this->getProductById($this->json['backingboard']['active_item']['id']);
            $weight = 0;
            if ($product) {
                $productModel = $this->productRepository->getById($this->product->getId());
                $weight = $productModel->getWeight();
            }
            //TODO: Have to add the backing board weight calculation
            $convertModel = $this->dataObject;
            $convertModel->setData('_updated_product_json_' . $product->getId(), $this->json);
            $graphicWidth = $this->getGraphicWidth($this->product);
            $graphicHeight = $this->getGraphicHeight($this->product);
            $area = (float)$graphicWidth * (float)$graphicHeight;
            $itemWeight = $weight * $area;
        }
        return (float)$itemWeight;
    }

    /**
     * Retrieve Graphic Width.
     *
     * @param Product|null $product Product Object.
     *
     * @return float
     */
    public function getGraphicWidth(Product $product = null)
    {
        $graphicWidth = 1;
        if (is_null($product)) {
            return 0;
        }
        if ($product) {
            $productJson = $this->getCurrentJson($product);
            switch ($productJson['size']['type']) {
                case 'frame':
                    $frameWidth = $this->sizeHelper->getInnerFrameWidth($productJson);
                    if (!empty($productJson['mat']['active_items'])) {
                        $mats = $productJson['mat']['active_items'];
                        $openingType = 'single';
                        if (!empty($productJson['mat']['openings']['type'])) {
                            $openingType = $productJson['mat']['openings']['type'];
                        }
                        switch ($openingType) {
                            case 'single' :
                                $matSizes = $productJson['mat']['sizes'];
                                $topMatSize = $this->fractionalToFloat(
                                    $matSizes['top']['integer'] . ' ' . $matSizes['top']['tenth']
                                );
                                $reveal = $this->fractionalToFloat($matSizes['reveal']);
                                foreach ($mats as $key => $mat) {
                                    $frameWidth = ($key == 'top')
                                        ? $frameWidth - $topMatSize * 2
                                        : $frameWidth - $reveal * 2;
                                }
                                $graphicWidth = $frameWidth;
                                break;
                            default :
                                $graphicWidth = $frameWidth;
                                break;
                        }
                    }
                    if (isset($productJson['mat']['overlap'])) {
                        $graphicWidth += $this->fractionalToFloat(
                                $productJson['mat']['overlap']
                            ) * 2;
                    }
                    break;
                case 'graphic':
                    $graphicWidth = $this->fractionalToFloat(
                        $productJson['size']['width']['integer'] . ' ' . $productJson['size']['width']['tenth']
                    );
                    break;
            }
        }
        return round((float)$graphicWidth, 4);
    }

    /**
     * Get customizer json for current product
     *
     * @param Product|integer $product Product object.
     *
     * @return mixed
     */
    public function getCurrentJson($product)
    {
        $productId = $product;
        if ($product instanceof Product) {
            if (!($product->getTypeId() == Type::TYPE_CODE)) {
                return null;
            }
            $productId = $product->getId();
        }
        if ($this->dataObject->hasData('_updated_product_json_' . $productId)) {
            return $this->dataObject->getData('_updated_product_json_' . $productId);
        }
        if ($this->dataObject->getData('current_json')) {
            return $this->dataObject->getData('current_json');
        }
        return [];
    }

    /**
     * Retrieve Graphic Height.
     *
     * @param Product|null $product Product Object.
     *
     * @return float
     */
    public function getGraphicHeight(Product $product = null)
    {
        $graphicHeight = 1;
        if (is_null($product)) {
            return 0;
        }
        if ($product) {
            $productJson = $this->getCurrentJson($product);
            switch ($productJson['size']['type']) {
                case 'frame':
                    $frameHeight = $this->sizeHelper->getInnerFrameHeight($productJson);
                    if (!empty($productJson['mat']['active_items'])) {
                        $mats = $productJson['mat']['active_items'];
                        $openingType = 'single';
                        if (!empty($productJson['mat']['openings']['type'])) {
                            $openingType = $productJson['mat']['openings']['type'];
                        }
                        switch ($openingType) {
                            case 'single' :
                                $matSizes = $productJson['mat']['sizes'];
                                $topMatSize = $this->fractionalToFloat(
                                    $matSizes['top']['integer'] . ' ' . $matSizes['top']['tenth']
                                );
                                $reveal = $this->fractionalToFloat($matSizes['reveal']);

                                $labelHeight = 0;
                                if (!empty($productJson['labels']['size']['height'])) {
                                    $labelHeight = $this->fractionalToFloat(
                                        $productJson['labels']['size']['height']
                                    );
                                }
                                $headerHeight = 0;
                                if (!empty($productJson['crheader']['size']['height'])) {
                                    $headerHeight = $this->fractionalToFloat(
                                        $productJson['crheader']['size']['height']
                                    );
                                }
                                if (!empty($headerHeight)) {
                                    if ($headerHeight < $topMatSize) {
                                        $headerHeight = $topMatSize;
                                    }
                                    $frameHeight -= $headerHeight + 1 + 0.25 + $topMatSize;
                                }
                                if (!empty($labelHeight)) {
                                    if ($labelHeight < $topMatSize) {
                                        $labelHeight = $topMatSize;
                                    }
                                    $frameHeight -= $labelHeight + 1 + 0.25 + $topMatSize;
                                }
                                if (empty($labelHeight) && empty($headerHeight) && $mats) {
                                    $frameHeight -= $topMatSize * 2;
                                }
                                foreach ($mats as $key => $mat) {
                                    $frameHeight = ($key == 'top')
                                        ? $frameHeight
                                        : $frameHeight - $reveal * 2;
                                }
                                $graphicHeight = $frameHeight;
                                break;
                            default :
                                $graphicHeight = $frameHeight;
                                break;
                        }
                    }
                    if (isset($productJson['mat']['overlap'])) {
                        $graphicHeight += $this->fractionalToFloat(
                                $productJson['mat']['overlap']
                            ) * 2;
                    }
                    break;
                case 'graphic':
                    $graphicHeight = $this->fractionalToFloat(
                        $productJson['size']['height']['integer'] . ' ' . $productJson['size']['height']['tenth']
                    );
                    break;
            }
        }

        return round((float)$graphicHeight, 4);
    }

    protected function calculateSimpleItemWeight()
    {
        $productModel = $this->productRepository->getById($this->product->getId());
        $weight = (float)$productModel->getWeight();
        return round($weight, 2);
    }

    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

}
