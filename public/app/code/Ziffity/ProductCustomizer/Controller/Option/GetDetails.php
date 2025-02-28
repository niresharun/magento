<?php

namespace Ziffity\ProductCustomizer\Controller\Option;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Ziffity\CustomFrame\Helper\Data as Helper;
use Ziffity\CustomFrame\Model\Product\Price;

class GetDetails extends \Magento\Framework\App\Action\Action
{

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ProductOptionRepositoryInterface
     */
    protected $optionsRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    protected $imageHelper;

    protected $storeManager;

    protected $pricingHelper;

    protected $helper;

    protected $priceModel;

    /**
     * @param Context
     * @param JsonFactory $resultJsonFactory
     * @param ProductOptionRepositoryInterface $optionsRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        ProductOptionRepositoryInterface $optionsRepository,
        ProductRepositoryInterface $productRepository,
        Context  $context,
        Image $imageHelper,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $pricingHelper,
        Helper $helper,
        Price $priceModel
    ) {

        $this->resultJsonFactory = $resultJsonFactory;
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->storeManager = $storeManager;
        $this->pricingHelper = $pricingHelper;
        $this->helper = $helper;
        $this->priceModel = $priceModel;
        parent::__construct($context);
    }

    public function execute()
    {
        $details = '';
        $post = $this->getRequest()->getParam('data');
        $selections = [];

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();

        if(isset($post['getDetails'])&& isset($post['selections'])){
            $selections = $post['selections'];

        };

        switch($post['type'])
        {
            case 'frame':
                $details = $this->getFrameDetails($post, $selections);
                break;
            case 'mat':
                $details = $this->getMatDetails($post, $selections);
                break;
            case 'top_mat':
                $details = $this->getMatDetails($post);
                break;
            case 'middle_mat':
                $details = $this->getMatDetails($post);
                break;
            case 'bottom_mat':
                $details = $this->getMatDetails($post);
                break;
            case 'dryerase_board':
                $details = $this->getDryeraseBoardDetails($post);
                break;
            case 'letter_board':
                $details = $this->getLetterBoardDetails($post);
                break;
            case 'chalk_board':
                $details = $this->getChalkBoardDetails($post);
                break;
            case 'cork_board':
                $details = $this->getCorkBoardDetails($post);
                break;
            case 'fabric':
                $details = $this->getFabricDetails($post);
                break;
            case 'laminate_exterior':
                $details = $this->getLaminateDetails($post);
                break;
            case 'laminate_interior':
                $details = $this->getLaminateDetails($post);
                break;

        }
        return $result->setData($details);

    }

    public function getFrameDetails($post, $selections = null)
    {
        $details = [];
        if ($post['ids']) {
            $description = '';
            $product = $this->productRepository->getById($post['ids']);
            $details['img_popup_preview_1'] = $this->loadImage($product, $product->getImgPopupPreview1(), $product->getImgPopupPreview1() ); //$this->getMediaUrl().$product->getImgPopupPreview1(); //$this->imageHelper->init($product, 'img_popup_preview_1')->getUrl();
            $details['img_popup_preview_2'] = $this->loadImage($product, $product->getImgPopupPreview2(), $product->getImgPopupPreview2() ); //$this->imageHelper->init($product, 'img_popup_preview_2')->getUrl();;
            $details['description'] = $product->getDescription();
            $details['code'] = $product->getCode();
            $details['width'] = $this->frameFraction($product->getLayerHeight());
            $details['height'] = $this->frameFraction($product->getFrameHeight());
            $details['name'] = $product->getName();
            $details['frame_material'] = $this->getAttributeLabels($product, 'frame_material', $product->getFrameMaterial());
            $details['frame_style'] = $this->getAttributeLabels($product, 'frame_style', $product->getFrameStyle());
            $details['frame_color'] = $this->getAttributeLabels($product,'frame_color',$product->getFrameColor());
            $price = !empty($post['price']) ? $post['price'] : (!empty($selections) ?
                $this->priceModel->getComponentPrice($product, $selections, 'frame'): $product->getPrice());
            $details['price'] = $this->pricingHelper->convertAndFormat($price, false, 2);
        }
        return $details;
//        $product->getResource()->getAttribute('frame_material')->getSource()->getOptionText();
    }

    public function getMatDetails($post, $selections = null)
    {
        $matDetails = [];
        if ($post['ids']) {
            $postIds = is_array($post['ids']) ? $post['ids']: explode(" ", $post['ids']);
            krsort($postIds);
            foreach($postIds as $key => $matType) {
                $details = [];
                $lebel = '';
                switch($key){
                    case 'top_mat':
                        $label = "Top Mat";
                        break;
                    case 'middle_mat':
                        $label = "Middle Mat";
                        break;
                    case 'bottom_mat':
                        $label = "Bottom Mat";
                        break;
                    default:
                        $label = 'Top Mat';
                }
                $id = is_array($matType) ? $matType['id'] : $matType;
                $product = $this->productRepository->getById($id);
                $price = !empty($post['price']) ? $post['price'] :
                    (is_array($matType) && !empty($matType['price']) ? $matType['price'] :(is_array($matType) && !empty($selections)?
                           $this->getMatPriceFromModel($product, $selections, $key)  : $product->getPrice()));
                $matDetails[$key] =
                [
                    'mat_code' => $label,
                    'thumb_img' => $this->loadImage($product, 'product_small_image', $product->getImgThumb() ),
                    'code' => $product->getCode(),
                    'name' => $product->getName(),
                    'mat_type' => $this->getAttributeLabels($product, 'mat_type',$product->getMatType()),
                    'mat_color' => $product->getResource()->getAttribute('mat_color')->getFrontend()->getValue($product),
                    'price' => $this->pricingHelper->convertAndFormat($price, false, 2)
                ];
            }
        }
        return $matDetails;
    }

    /**
     * @param $product
     * @param $selections
     * @param $matType
     * @return mixed
     */
    public function getMatPriceFromModel($product, $selections, $matType)
    {
       $prices =  $this->priceModel->getComponentPrice($product, $selections, 'mat');
       return $prices[$matType];
    }

    /**
     * @param $post
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getChalkBoardDetails($post)
    {
        if ($post['ids']) {
            $description = '';
            $product = $this->productRepository->getById($post['ids']);
            $details['description'] = $product->getDescription();
            $details['code'] = $product->getCode();
            $details['thumb_img'] = $this->loadImage($product, 'product_base_image', $product->getImgThumb());
            $details['name'] = $product->getName();
            $details['dry_erase_board_material'] = $this->getAttributeLabels($product, 'dry_erase_board_material', $product->getDryEraseBoardMaterial());
            $details['board_color'] = $this->getAttributeLabels($product, 'board_color', $product->getBoardColor());
            $price = isset($post['price']) ? $post['price'] : $product->getPrice();
            $details['price'] = $this->pricingHelper->convertAndFormat($price, false, 2);
        }
        return $details;
    }

    /**
     * @param $post
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCorkBoardDetails($post)
    {
        if ($post['ids']) {
            $description = '';
            $product = $this->productRepository->getById($post['ids']);
            $details['description'] = $product->getDescription();
            $details['code'] = $product->getCode();
            $details['thumb_img'] = $this->loadImage($product, 'product_base_image', $product->getImgThumb());
            $details['name'] = $product->getName();
            $details['dry_erase_board_material'] = $this->getAttributeLabels($product, 'dry_erase_board_material', $product->getDryEraseBoardMaterial());
            $details['board_color'] = $this->getAttributeLabels($product, 'board_color', $product->getBoardColor());
            $price = isset($post['price']) ? $post['price'] : $product->getPrice();
            $details['price'] = $this->pricingHelper->convertAndFormat($price, false, 2);
        }
        return $details;
    }

    /**
     * @param $post
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDryeraseBoardDetails($post)
    {
        if ($post['ids']) {
            $description = '';
            $product = $this->productRepository->getById($post['ids']);
            $details['description'] = $product->getDescription();
            $details['code'] = $product->getCode();
            $details['thumb_img'] = $this->loadImage($product, 'product_base_image', $product->getImgThumb());
            $details['name'] = $product->getName();
            $details['dry_erase_board_material'] = $this->getAttributeLabels($product, 'dry_erase_board_material', $product->getDryEraseBoardMaterial());
            $details['board_color'] = $this->getAttributeLabels($product, 'board_color', $product->getBoardColor());
            $price = isset($post['price']) ? $post['price'] : $product->getPrice();
            $details['price'] = $this->pricingHelper->convertAndFormat($price, false, 2);
        }
        return $details;
    }

    /**
     * @param $post
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFabricDetails($post)
    {
        if ($post['ids']) {
            $description = '';
            $product = $this->productRepository->getById($post['ids']);
            $details['description'] = $product->getDescription();
            $details['code'] = $product->getCode();
            $details['thumb_img'] = $this->loadImage($product, 'product_base_image', $product->getImgThumb());
            $details['name'] = $product->getName();
            $details['fabric_style'] = $this->getAttributeLabels($product, 'fabric_style', $product->getFabricStyle());
            $details['fabric_color'] = $this->getAttributeLabels($product, 'board_color', $product->getFabricColor());
            $price = isset($post['price']) ? $post['price'] : $product->getPrice();
            $details['price'] = $this->pricingHelper->convertAndFormat($price, false, 2);
        }
        return $details;
    }

    /**
     * @param $post
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLetterBoardDetails($post)
    {
        if ($post['ids']) {
            $description = '';
            $product = $this->productRepository->getById($post['ids']);
            $details['description'] = $product->getDescription();
            $details['code'] = $product->getCode();
            $details['thumb_img'] = $this->loadImage($product, 'product_base_image', $product->getImgThumb());
            $details['name'] = $product->getName();
            $details['letter_board_material'] = $this->getAttributeLabels($product, 'letter_board_material', $product->getLetterBoardMaterial());
            $details['board_color'] = $this->getAttributeLabels($product, 'board_color', $product->getBoardColor());
            $price = isset($post['price']) ? $post['price'] : $product->getPrice();
            $details['price'] = $this->pricingHelper->convertAndFormat($price, false, 2);
        }
        return $details;
    }

    /**
     * @param $post
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLaminateDetails($post)
    {
        if ($post['ids']) {
            $description = '';
            $product = $this->productRepository->getById($post['ids']);
            $details['code'] = $product->getCode();
            $details['thumb_img'] = $this->loadImage($product, 'product_base_image', $product->getImgThumb());
            $details['name'] = $product->getName();
            $details['laminate_type'] = $this->getAttributeLabels($product, 'laminate_type', $product->getLaminateType());
            $price = isset($post['price']) ? $post['price'] : $product->getPrice();
            $details['price'] = $this->pricingHelper->convertAndFormat($price, false, 2);
        }
        return $details;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @param $product
     * @param $type
     * @param $prodImage
     * @return string
     */
    public function loadImage($product, $type, $prodImage)
    {
        $urls = null;
        $baseImage = $this->imageHelper->init($product, $type)
            ->setImageFile($prodImage)
            ->resize(215, 144)
            ->getUrl();
        $urls = $baseImage;
        return $urls;
    }

    /**
     * @param $data
     * @return string
     */
    public function frameFraction($data)
    {
        $data = $this->helper->floatToFractional($data);
        $fraction = ' ';
        if($data) {
            isset($data['decimal']) && ($data['decimal']>0) ? $fraction .= $data['decimal']: 0;
            isset($data['fractional']['top']) && ($data['fractional']['top']>0)?
                $fraction.= ' '.$data['fractional']['top'] : 0;
            isset($data['fractional']['bottom']) && ($data['fractional']['top']>0) ?
                $fraction .= '/'. $data['fractional']['bottom'] : 0;
            $fraction .= '"';
        }
        return $fraction;
    }

    /**
     * @param $product
     * @param $attribute
     * @param $options
     * @return string
     */
    public function getAttributeLabels($product, $attribute, $options)
    {
        $attributeLabels = '';
        $label = [];
        if(isset($options)) {
            $options = is_array($options) ? $options : explode(" ", $options);
            foreach ($options as $option) {
                if ($product) {
                    array_push($label, $product->getResource()->getAttribute($attribute)->getSource()->getOptionText($option));
                }
            }
        }
        return implode(", ",$label);
    }
}
