<?php

namespace Ziffity\ProductCustomizer\Block\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Data\Form\FormKey;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
use Ziffity\ProductCustomizer\Helper\Data;
use Ziffity\ProductCustomizer\Model\CompositeConfigProvider;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Registry;
use Ziffity\ProductCustomizer\Model\ResourceModel\Entity\Attribute\MultiSelectOptionValueProvider;

/**
 * PDP ProductCustomizer block
 */
class View extends \Magento\Framework\View\Element\Template
{

    /**
     * @var ProductOptionRepositoryInterface
     */
    protected $optionsRepository;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var bool
     */
    protected $_isScopePrivate = false;

    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @var CompositeConfigProvider
     */
    protected $configProvider;

    /**
     * @var array|LayoutProcessorInterface[]
     */
    protected $layoutProcessors;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Data
     */
    public $customizerHeleper;

    /**
     * @var MultiSelectOptionValueProvider
     */
    protected $multiselectModel;

    /**
     * @param Context $context
     * @param FormKey $formKey
     * @param CompositeConfigProvider $configProvider
     * @param Registry $registry
     * @param ProductOptionRepositoryInterface $optionsRepository
     * @param Data $customizerHelper
     * @param MultiSelectOptionValueProvider $multiselectModel
     * @param array $layoutProcessors
     * @param array $data
     * @param Json|null $serializer
     * @param SerializerInterface|null $serializerInterface
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        CompositeConfigProvider $configProvider,
        Registry $registry,
        ProductOptionRepositoryInterface $optionsRepository,
        Data $customizerHelper,
        MultiSelectOptionValueProvider $multiselectModel,
        array $layoutProcessors = [],
        array $data = [],
        Json $serializer = null,
        SerializerInterface $serializerInterface = null
    ) {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
        $this->_isScopePrivate = true;
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->configProvider = $configProvider;
        $this->customizerHeleper = $customizerHelper;
        $this->multiselectModel = $multiselectModel;
        $this->layoutProcessors = $layoutProcessors;
        $this->serializer = $serializerInterface ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\JsonHexTag::class);
        $this->registry = $registry;
        $this->optionsRepository = $optionsRepository;
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
     * @inheritdoc
     */
    public function getJsLayout()
    {
        $this->modifyJsLayout();
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }

        return $this->serializer->serialize($this->jsLayout);
    }

    /**
     * @return void
     */
    public function modifyJsLayout()
    {
        if (isset($this->jsLayout['components']['customizer'])) {
            foreach ($this->jsLayout['components']['customizer'] as $key => $value) {
                if ($key == "children") {
                    if (isset($value['option-renderer'])) {
                        $this->jsLayout['components']['customizer'][$key]
                        ['option-renderer']['children'] =
                            $this->addOptionChildren($value['option-renderer']);
                    }
                }
            }
        }
    }

    /**
     * @param $title
     * @return array|string|string[]
     */
    public function getCode($title)
    {
        $code = '';
        if ($title) {
            $code = strToLower($title);
            $code = str_replace(" ", "_", $code);
        }
        return $code;
    }

    public function addOptionChildren($optionRenderer)
    {
        $mats = ['Top Mat', 'Middle Mat', 'Bottom Mat'];
        $laminates = ['Laminate Exterior','Laminate Interior'];
        $optionRenderer['Size'] = $this->buildMandatoryComponents('Size');
//       $optionRenderer['Shelves'] = $this->buildMandatoryComponents('Shelves');
//        $optionRenderer['Image'] = $this->buildMandatoryComponents('Image');
//        $optionRenderer['Size'] = $this->buildMandatoryComponents('Size');
         $index = 2;
        foreach ($this->optionsRepository->getList($this->getProductSku()) as $option) {
            if (in_array($option->getTitle(), $mats)) {
                $optionRenderer = $this->groupMats($option->getTitle(), $option->getPosition(), $optionRenderer);
                continue;
            }
            if (in_array($option->getTitle(),$laminates)){
                $optionRenderer = $this->groupLaminates($option->getTitle(), $option->getPosition(), $optionRenderer);
                continue;
            }
            $children = $this->buildComponent($option->getTitle(), $option->getPosition());
            if (!empty($children)) {
                $optionRenderer[$this->getCode($option->getTitle())] = $children;
                $index += 1;
            }
        }
        $product = $this->getProduct();

        if($product->getAdditionalTabs()) {
            $additionalTabs = $this->multiselectModel->getMultiple($product->getAdditionalTabs());
            foreach ($additionalTabs as $tab) {
                $children = $this->buildComponent($tab, $index);
                if (!empty($children)) {
                    $optionRenderer[$this->getCode($tab)] = $children;
                    $index += 1;
                }
            }
        }

        $optionRenderer = $this->getHeadersComponent($optionRenderer);
        $optionRenderer = $this->getLabelsComponent($optionRenderer);
        return $optionRenderer;
    }

    public function getHeadersComponent($result): array
    {
        if (isset($result['headers'])) {
            $textHeader = ['component' => 'Ziffity_ProductCustomizer/js/view/options/text-header',
                'sortOrder' => 1,
                'config' => [
                    'template' => 'Ziffity_ProductCustomizer/options/text-header',
                    'deps' => ['customizerProvider']
                ]
            ];
            $result['headers']['children']['text-header'] = $textHeader;
            $imageHeader = ['component' => 'Ziffity_ProductCustomizer/js/view/options/image-header',
                'sortOrder' => 2,
                'config' => [
                    'template' => 'Ziffity_ProductCustomizer/options/image-header',
                    'deps' => ['customizerProvider']
                ]
            ];
            $result['headers']['children']['image-header'] = $imageHeader;
        }
        return $result;
    }

    public function getLabelsComponent($result): array
    {
        if (isset($result['labels'])) {
            $textHeader = ['component' => 'Ziffity_ProductCustomizer/js/view/options/text-label',
                'sortOrder' => 1,
                'config' => [
                    'template' => 'Ziffity_ProductCustomizer/options/text-label',
                    'deps' => ['customizerProvider']
                ]
            ];
            $result['labels']['children']['text-label'] = $textHeader;
            $imageHeader = ['component' => 'Ziffity_ProductCustomizer/js/view/options/image-label',
                'sortOrder' => 2,
                'config' => [
                    'template' => 'Ziffity_ProductCustomizer/options/image-label',
                    'deps' => ['customizerProvider']
                ]
            ];
            $result['labels']['children']['image-label'] = $imageHeader;
        }
        return $result;
    }

    public function buildMandatoryComponents($title)
    {
        switch ($title) {
            case 'Size':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/size',
                        'sortOrder'=>'1',
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/size',
                            'deps'=>['customizerProvider']]];
            case 'Image':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/image',
                        'sortOrder'=>'1',
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/image',
                            'deps'=>['customizerProvider']]];
            case 'Shelves':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/shelves',
                        'sortOrder'=>'1',
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/shelves',
                            'deps'=>['customizerProvider']]];
        }
        return [];
    }

    public function buildComponent($title, $position): array
    {
        switch ($title) {
            case 'Accessories':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/accessories',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/accessories',
                            'deps'=>['customizerProvider']]];
            case 'Addons':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/addons',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/addons',
                            'deps'=>['customizerProvider']]];
            case 'Backing Board':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/backing-board',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/backing-board',
                            'deps'=>['customizerProvider']]];
            case 'Chalkboards':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/chalk-boards',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/chalk-boards',
                            'deps'=>['customizerProvider']]];
            case 'Corkboards':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/cork-boards',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/cork-boards',
                            'deps'=>['customizerProvider']]];
            case 'Dryerase Board':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/dryerase-boards',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/dryerase-boards',
                            'deps'=>['customizerProvider']]];
            case 'Fabric':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/fabric',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/fabric',
                            'deps'=>['customizerProvider']]];
            case 'Frame':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/frame',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/frame',
                            'deps'=>['customizerProvider']]];
            case 'Glass':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/glass',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/glass',
                            'deps'=>['customizerProvider']]];
            case 'Headers':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/header',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/header',
                            'deps'=>['customizerProvider']]];
            case 'Labels':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/label',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/label',
                            'deps'=>['customizerProvider']]];
            case 'Laminate Exterior':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/laminate-exterior',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/laminate-exterior',
                            'deps'=>['customizerProvider']]];
            case 'Laminate Interior':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/laminate-interior',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/laminate-interior',
                            'deps'=>['customizerProvider']]];
            case 'Letter Board':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/letter-board',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/letter-board',
                            'deps'=>['customizerProvider']]];
            case 'Openings':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/opening',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/opening',
                            'deps'=>['customizerProvider']]];
            case 'Post Finish':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/post-finish',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/post-finish',
                            'deps'=>['customizerProvider']]];
            case 'Shelves':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/shelves',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/shelves',
                            'deps'=>['customizerProvider']]];
            case 'Lighting':
                return
                    ['component' => 'Ziffity_ProductCustomizer/js/view/options/lighting',
                        'sortOrder'=> $position,
                        'config'=>[
                            'template'=>'Ziffity_ProductCustomizer/options/lighting',
                            'deps'=>['customizerProvider']]];
            default:
                return [];
        }
    }


    /**
     * Group mat types
     */
    public function groupMats($title, $position, $optionRenderer)
    {
        if (!isset($optionRenderer['mat'])) {
            $optionRenderer['mat'] =
            ['component' => 'Ziffity_ProductCustomizer/js/view/options/mat',
            'sortOrder'=> $position,
            'config'=>[
                'template'=>'Ziffity_ProductCustomizer/options/mat',
                'deps'=>['customizerProvider']],
            'children'=> []
            ];
        }
        list($componentName, $config) = $this->getMatComponent($title);
        $optionRenderer['mat']['children'][$componentName] = $config;
        return $optionRenderer;
    }

    /**
     * This function groups laminate types.
     *
     * @param string $title
     * @param string $position
     * @param mixed $optionRenderer
     */
    public function groupLaminates(string $title, string $position, mixed $optionRenderer)
    {
        if (!isset($optionRenderer['laminate'])) {
            $optionRenderer['laminate'] =
                ['component' => 'Ziffity_ProductCustomizer/js/view/options/laminate',
                    'sortOrder'=> $position,
                    'config'=>[
                        'template'=>'Ziffity_ProductCustomizer/options/laminate',
                        'deps'=>['customizerProvider']],
                    'children'=> []
                ];
        }
        list($componentName, $config) = $this->getLaminateComponent($title);
        $optionRenderer['laminate']['children'][$componentName] = $config;
        return $optionRenderer;
    }

    public function getMatComponent($title)
    {
        switch ($title) {
            case 'Top Mat':
                $topMat = ['component' => 'Ziffity_ProductCustomizer/js/view/options/top-mat',
                'sortOrder'=> 1,
                'config'=>[
                    'template'=>'Ziffity_ProductCustomizer/options/top-mat',
                    'deps'=>['customizerProvider']
                    ]
                ];
                return ['top-mat', $topMat];
            case 'Middle Mat':
                $middleMat = ['component' => 'Ziffity_ProductCustomizer/js/view/options/middle-mat',
                'sortOrder'=> 2,
                'config'=>[
                    'template'=>'Ziffity_ProductCustomizer/options/middle-mat',
                    'deps'=>['customizerProvider']
                    ]
                ];
                return ['middle-mat', $middleMat];
            case 'Bottom Mat':
                $bottomMat = ['component' => 'Ziffity_ProductCustomizer/js/view/options/bottom-mat',
                'sortOrder'=> 3,
                'config'=>[
                    'template'=>'Ziffity_ProductCustomizer/options/bottom-mat',
                    'deps'=>['customizerProvider']
                    ]
                ];
                return ['bottom-mat', $bottomMat];
            default:
                return [];
        }
    }

    public function getLaminateComponent($title): array
    {
        switch ($title) {
            case 'Laminate Exterior':
                $laminateExterior = ['component' => 'Ziffity_ProductCustomizer/js/view/options/laminate-exterior',
                    'sortOrder'=> 1,
                    'config'=>[
                        'template'=>'Ziffity_ProductCustomizer/options/laminate-exterior',
                        'deps'=>['customizerProvider']
                    ]
                ];
                return ['laminate-exterior', $laminateExterior];
            case 'Laminate Interior':
                $laminateInterior = ['component' => 'Ziffity_ProductCustomizer/js/view/options/laminate-interior',
                    'sortOrder'=> 2,
                    'config'=>[
                        'template'=>'Ziffity_ProductCustomizer/options/laminate-interior',
                        'deps'=>['customizerProvider']
                    ]
                ];
                return ['laminate-interior', $laminateInterior];
            default:
                return [];
        }
    }

    /**
     * Get product id
     *
     * @return int|null
     */
    public function getProductSku()
    {
        $product = $this->registry->registry('product');
        return $product ? $product->getSku() : null;
    }

    /**
     * Retrieve form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Retrieve customizer configuration
     *
     * @return array
     */
    public function getCustomizerConfig()
    {
        return $this->configProvider->getConfig();
    }

    /**
     * Get base url for block.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Retrieve serialized customframe config.
     *
     * @return bool|string
     */
    public function getSerializedCustomizerConfig()
    {
        return  $this->serializer->serialize($this->getCustomizerConfig());
    }
}
