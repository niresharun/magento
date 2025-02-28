<?php

namespace Ziffity\ProductCustomizer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Ziffity\ProductCustomizer\Helper\Data;
use Ziffity\ProductCustomizer\Model\Components\Measurements\FrameSize;
use Magento\Framework\Serialize\Serializer\Json;

class Selections extends AbstractHelper
{

    /**
     * @var ProductOptionRepositoryInterface
     */
    protected $optionsRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    protected $_floatToFractionalHash = [];

    /**
     *
     * @var Registry
     */
    protected $registry;

    protected $imageHelper;

    protected $storeManager;

    protected $frameSize;

    protected $helper;

    protected $serializer;

    /**
     * @param ProductOptionRepositoryInterface $optionsRepository
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Context $context,
        ProductOptionRepositoryInterface $optionsRepository,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Registry $registry,
        Image $imageHelper,
        StoreManagerInterface $storeManager,
        FrameSize $frameSize,
        Data $helper,
        Json $serializer,
    ) {
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->imageHelper = $imageHelper;
        $this->storeManager = $storeManager;
        $this->frameSize = $frameSize;
        $this->helper = $helper;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    public function getSelections($options, $product, $completedSteps)
    {
        $attributes = [];
            foreach ($completedSteps as $key => $step) {
                switch($step) {
                    case "size" :
                        $attributes['size'] = $this->getSizeSelections($product, $options);
                        if(!empty($options['size']['thickness'])) {
                            $attributes['thickness'] = $this->getThickness($product, $options);
                        }
                        break;
                    case "frame":
                        try {
                            $attributes['overall_frame_size'] = $this->getOverallFrameSize($product, $options);
//                            $attributes['inner_frame_size'] = $this->getInnerFrameSize($product, $options);
                            $attributes['Frame'] = $this->getFrameSelections($product, $options);
                        } catch (\Exception $e) {
                            $attributes['frame'] = [
                                'label' => _('Overall Frame Size'),
                                'value' => 'yet to be calculated'
                            ];
                        }
                        break;
                    case "mat":
                        $attributes['Mat'] = $this->getMatSelections($product, $options);
                        break;
                    case "laminate_finish":
                        $attributes['Laminate Finish'] = $this->getLaminateSelections($product, $options);
                        break;
                    case "addons" :
                        $attributes['Add-ons'] = $this->getAddonsSelections($product, $options);
                        break;
                    case "shelves" :
                        $shelves = $this->getShelvesSelections($product, $options);
                        if (!empty($shelves)) {
                            $attributes['Shelves'] = $shelves;
                        }
                        break;
                    case "lighting":
                        $lighting = $this->getLightingSelections($product, $options);
                        if (!empty($lighting)) {
                            $attributes['Lighting'] = $lighting;
                        }
                        break;
                    case "accessories":
                        $attributes['Accessories'] = $this->getAccessoriesSelections($product, $options);
                        break;
                    case "header":
                        $attributes['Header'] = ['label' => 'Header', "value" => 'Yes'];
                        break;
                    case "image-labels":
                        $attributes['Labels'] = ['label' => 'Labels', "value" => 'Yes'];
                        break;
                    default:
                        if(!in_array($step, ['openings'])) {
                            (isset($options[$step]) && isset($options[$step]['active_item']) && !empty($options[$step]['active_item'])) ? $attributes[$step] = [
                                'label' => $key,
                                'value' => $options[$step]['active_item']['name']
                            ] : '';
                        }
                }
            }
        return $attributes;
    }

    public function getSizeSelections($product, $options)
    {
        $attributes = [];
        $width = '';
        if ($options['size']['width']['integer']) {
            $width = $options['size']['width']['integer'] . ' ';
        }
        if ($options['size']['width']['tenth']) {
            $width .= $options['size']['width']['tenth'];
        }
        $height = '';
        if ($options['size']['height']['integer']) {
            $height = $options['size']['height']['integer'] . ' ';
        }
        if ($options['size']['height']['tenth']) {
            $height .= $options['size']['height']['tenth'];
        }
        $product = $this->productRepository->get($product->getSku());
        $attributeNickname = $product->getResource()->getAttribute('nickname_sizes');
        $sizeNickname = '';
        if ($attributeNickname->getId()) {
            $sizeNickname = $attributeNickname->getFrontend()->getValue($product) . ' ';
        }
        return [
            'label' => $sizeNickname . _('Size'),
            'value' => trim($width) . '&quot; by ' . trim($height) . '&quot;'
        ];
        return $attributes;
    }


    public function getOverallFrameSize($product, $options)
    {
        $overallFrameWidth = $this->frameSize->getOverallWidth($options);
        $overallFrameHeight = $this->frameSize->getOverallHeight($options);

        return [
            'label' => _('Overall Frame Size'),
            'value' => $overallFrameWidth . '&quot; by ' .
                $overallFrameHeight. '&quot;'
        ];
    }

    public function getInnerFrameSize($product, $options)
    {
        $overallFrameWidth = $this->frameSize->getInnerFrameWidth($options);
        $overallFrameHeight = $this->frameSize->getInnerFrameHeight($options);
        $widthvalue =  $this->helper->floatToFractional($overallFrameWidth);
        $innerFrameWidthInteger = $widthvalue['decimal'];
        $innerFrameWidthFractional = (!$widthvalue['fractional']['top']) ? 0 : $widthvalue['fractional']['top'] .'/'.
            $widthvalue['fractional']['bottom'];
        $heightvalue =  $this->helper->floatToFractional($overallFrameHeight);
        $overallFrameHeightInteger = $heightvalue['decimal'];
        $overallFrameHeightFractional = (!$heightvalue['fractional']['top']) ? 0 : $heightvalue['fractional']['top'] .'/'.
            $heightvalue['fractional']['bottom'];
        return [
            'label' => _('Inner Frame Size'),
            'value' =>  $innerFrameWidthInteger.' ' .$innerFrameWidthFractional . '&quot; by ' .
                $overallFrameHeightInteger.' '.$overallFrameHeightFractional. '&quot;'
        ];
    }

    public function getThickness($product, $options)
    {
        $attributes = [];
        if ($product->getDepthType() && $product->getDepthType() !== 'none') {
            if (!empty($options['size']['thickness'])) {
                $value = $options['size']['thickness'];
            }
            return [
                'label' => ($product->getDepthType() === 'graphic_thickness') ?_('Graphic Thickness')
                    : _('Interior Depth'),
                'value' => trim($value) . '&quot;'
            ];
        }
        return $attributes;
    }

    public function getFrameSelections($product, $options)
    {
        if (!empty($options['frame']['active_item'])) {
            $item = $options['frame']['active_item'];
            $frameOverlap = $this->frameSize->getFrameOverlap($options);
//            $frameOverlap =  $this->helper->floatToFractional($frameOverlap);
//            $decimal = $frameOverlap['decimal'];
//            $fractional = (!$frameOverlap['fractional']['top']) ? 0 : $frameOverlap['fractional']['top'] .'/'.
//                $frameOverlap['fractional']['bottom'];
            return [
                'label' => __('Frame'), 'value' => $item['name']
            ];

        }
        return [];
    }

    public function getMatSelections($product, $options)
    {
        $attributes = [];
        if (!empty($options['mat']['active_items']['top_mat']['id'])) {
            $item = $options['mat']['active_items']['top_mat'];
            $attributes[] = ['label' => __('Top'), 'value' => $item['name']];
        }
        if (!empty($options['mat']['active_items']['middle_mat']['id'])) {
            $item = $options['mat']['active_items']['middle_mat'];
            $attributes[] = ['label' => __('Middle'), 'value' => $item['name']];
        }
        if (!empty($options['mat']['active_items']['bottom_mat']['id'])) {
            $item = $options['mat']['active_items']['bottom_mat'];
            $attributes[] = ['label' => __('Bottom'), 'value' => $item['name']];
        }
        return $attributes;
    }

    public function getLaminateSelections($product, $options)
    {
        $attributes = [];
        if (!empty($options['laminate_finish']['active_items']['laminate_exterior']['id'])) {
            $item = $options['laminate_finish']['active_items']['laminate_exterior'];
            $attributes[] = ['label' => __('Exterior'), 'value' => $item['name']];
        }
        if (!empty($options['laminate_finish']['active_items']['laminate_interior']['id'])) {
            $item = $options['laminate_finish']['active_items']['laminate_interior'];
            $attributes[] = ['label' => __('Interior'), 'value' => $item['name']];
        }
        return $attributes;
    }

    public function getAddonsSelections($product, $options)
    {
        $attributes = [];
        $values = [
            'plunge_lock'    => [
                'label'  => __('Plunge Lock'),
                'values' => [
                    'include' => [
                        'label' => __('Include Plunge lock')
                    ],
                    'no'      => [
                        'label' => __('No plunge lock')
                    ],
                ]
            ],
            'hinge_position' => [
                'label'  => __('Hinge Position'),
                'values' => [
                    'top'   => [
                        'label' => __('Top Hinge')
                    ],
                    'right' => [
                        'label' => __('Right Hinge')
                    ],
                    'left'  => [
                        'label' => __('Left Hinge')
                    ],
                ]
            ],
        ];
        $return = [];
        $addons = !empty($options['addons'])?$options['addons'] : [];
        foreach ($this->getFormData($addons['form_data']) as $key => $value) {
            $return[] = [
                'label' => $values[$key]['label'],
                'value' => $values[$key]['values'][$value]['label'],
            ];
        }
        return $return;
    }

    public function getLightingSelections($product, $options)
    {
        $attributes = [];
        $values = [
            'lighting_position'    => [
                'label'  => __('Lighting Position'),
                'values' => [
                    'top' => [
                        'label' => __('Interior Top Lit')
                    ],
                    'perimeter'      => [
                        'label' => __('Interior Perimeter Lit')
                    ],
                ]
            ],
            'power_connection'  => [
                'label'  => __('Power Connection'),
                'values' => [
                    'plug'      => [
                        'label' => __('Plug'),
                    ],
                    'hardwired' => [
                        'label' => __('Hardwired'),
                    ],
                ],
            ],
            'cord_color'        => [
                'label'  => __('Cord Color'),
                'values' => [
                    'black' => [
                        'label' => __('Black'),
                    ],
                    'white' => [
                        'label' => __('White'),
                    ],
                ],
            ],
        ];
        $return = [];
        $lighting = !empty($options['lighting'])?$options['lighting'] : [];
        foreach ($this->getLightingFormData($lighting['form_data']) as $key => $value) {
            $return[] = [
                'label' => $values[$key]['label'],
                'value' => $values[$key]['values'][$value]['label'],
            ];
        }
        return $return;
    }

     public function getAccessoriesSelections($product, $options)
     {
         $attributes = [];
         $accessories = '';
         if(isset($options['accessories']['active_items'])) {
             foreach ($options['accessories']['active_items'] as $activeItem) {
                 array_push($attributes,  $activeItem['name']);
             }

             $accessories = implode(', ', $attributes);
             return [
                 'label' => _('Accessories'),
                 'value' => $accessories
             ];
         }

//         if (!empty($options['accessories']['active_items']['top_mat']['id'])) {
//             $item = $options['mat']['active_items']['top_mat'];
//             $attributes[] = ['label' => __('Top'), 'value' => $item['name']];
//         }
//         if (!empty($options['mat']['active_items']['middle_mat']['id'])) {
//             $item = $options['mat']['active_items']['middle_mat'];
//             $attributes[] = ['label' => __('Middle'), 'value' => $item['name']];
//         }
//         if (!empty($options['mat']['active_items']['bottom_mat']['id'])) {
//             $item = $options['mat']['active_items']['bottom_mat'];
//             $attributes[] = ['label' => __('Bottom'), 'value' => $item['name']];
//         }
         return $accessories;
     }

    protected function getLightingFormData($lighting)
    {
        $formData = [
            'lighting_position'  => 'top',
            'power_connection'  => 'hardwired',
            'cord_color'=> 'black',
        ];
        if (!empty($lighting)) {
            $formData = [
                'lighting_position'  => $lighting['lighting_position'],
                'power_connection'  => $lighting['power_connection'],
                'cord_color'=> $lighting['cord_color']
            ];
        }
        return $formData;
    }

    protected function getFormData($addons)
    {
        $formData = [
            'plunge_lock' => 'no',
            'hinge_position' => 'left'
        ];
        if (!empty($addons)) {
            $formData = [
                'plunge_lock' => $addons['plunge_lock'],
                'hinge_position' => $addons['hinge_position']
            ];
        }
        return $formData;
    }

    public function getShelvesSelections($product, $options)
    {
        $values = $this->getAvailableFormValues();
        $return = [];
        $shelves = !empty($options['shelves'])?$options['shelves'] : [];
        foreach ($this->getShelfFormData($shelves) as $value) {
            if ('shelves_qty' === $value['name'] && 0 == $value['value']) {
                $return = [];
                break;
            }
            $return[] = [
                'label' => $values[$value['name']]['label'],
                'value' => $values[$value['name']]['values'][$value['value']]['label']
            ];
        }

        return $return;
    }

    protected function getShelfFormData($shelves)
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

        if (!empty($shelves) && isset($shelves['shelves_thickness'])) {
            $formData = [
                0 => [
                    'name'  => 'shelves_qty',
                    'value' => $shelves['shelves_qty'],
                ],
                1 => [
                    'name'  => 'shelves_thickness',
                    'value' => $shelves['shelves_thickness'],
                ],
            ];
            return $formData;
        }
        return $formData;
    }

    /**
     * Get available form values
     *
     * @return array
     */
    public function getAvailableFormValues()
    {
        return [
            'shelves_qty'       => [
                'label'  => __('Number of Shelves'),
                'values' => [
                    '0' => [
                        'label' => __('0')
                    ],
                    '1' => [
                        'label' => __('1')
                    ],
                    '2' => [
                        'label' => __('2')
                    ],
                    '3' => [
                        'label' => __('3')
                    ],
                    '4' => [
                        'label' => __('4')
                    ],
                    '5' => [
                        'label' => __('5')
                    ],
                    '6' => [
                        'label' => __('6')
                    ],
                ]
            ],
            'shelves_thickness' => [
                'label'  => __('Thickness'),
                'values' => [
                    '0.25' => [
                        'label' => __('1/4&quot;')
                    ],
                    '0.375' => [
                        'label' => __('3/8&quot;')
                    ],
                ]
            ],
        ];
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    public function loadImage($product, $type, $prodImage)
    {
        $urls = null;
        // foreach ($product->getMediaGalleryEntries() as $image) {
        $baseImage = $this->imageHelper->init($product, $type)
            ->setImageFile($prodImage)
            ->getUrl();
        $urls = $baseImage;
        //}
        return $urls;
    }

    public function getCompletedStepsFromOptions($options)
    {
        $steps = [];
        $keyToSkip = ['additional_data','original_additional_data', 'openings'];
        $options = $this->getUnserializedData($options);
        foreach($options as $key => $option){
            if(!in_array($key,$keyToSkip)) {
                $steps[$this->getLabelFromKey($key)] = $key;
            }
        }
        return $steps;

    }

    public function getUnserializedData($data)
    {
        $unserializedData = [];
        if($data){
            $unserializedData = $this->serializer->unserialize($data);
        }
        return $unserializedData;
    }

    public function getLabelFromKey($key)
    {
        switch($key){
            case 'frame':
                $label = 'Frame';
                break;
            case 'addons':
                $label = 'Add-on';
                break;
            case 'mat':
                $label = 'Mat';
                break;
            case 'cork_board':
                $label = 'Cork Board';
                break;
            case 'letter_board':
                $label = 'Letter Board';
                break;
            case 'dryerase_board':
                $label = 'Dry Erase Board';
                break;
            case 'chalk_board':
                $label = 'Chalk Board';
                break;
            case 'glass':
                $label = 'Glass/Glazing';
                break;
            case 'post_finish':
                $label = 'Post Finish';
                break;
            case 'fabric':
                $label = 'Fabric';
                break;
            case 'lighting':
                $label = 'Lighting';
                break;
            case 'laminate_finish':
                $label = 'Laminate Finish';
                break;
            case 'backing_board':
                $label = 'Backing Board';
                break;
            case 'header':
                $label = 'Header';
                break;
            case 'image-labels':
                $label = 'Labels';
                break;
            default:
                $label = 'No Label';
                break;
        }
        return $label;
    }

    /**
     * @param $productId
     * @return ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct($productId)
    {
        return $this->productRepository->getById($productId);
    }

}
