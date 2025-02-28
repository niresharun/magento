<?php

namespace Ziffity\ProductCustomizer\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Ziffity\CustomFrame\Model\Product\Attribute\Source\Type\MatboardTopInteger;
use Ziffity\CustomFrame\Model\Product\Attribute\Source\Type\MatboardTopFraction;
use Ziffity\ProductCustomizer\Helper\Data;
use Ziffity\ProductCustomizer\Helper\Mat;
use Magento\Framework\Data\Form\FormKey;
use \Magento\Catalog\Block\Product\ListProduct;
use Magento\Checkout\Helper\Cart;
use Amasty\RequestQuote\Helper\Cart as QuoteCart;
use \Magento\Quote\Model\Quote\ItemFactory;
use \Magento\Quote\Model\ResourceModel\Quote\Item;
use Magento\Framework\App\Request\Http;
use Amasty\RequestQuote\Helper\Data as QuoteHelper;

/**
 * Default Config Provider for customframe
 */
class DefaultConfigProvider implements ConfigProviderInterface
{

    const  CO_PRODUCTS = "Co-Products";

    /**
     * @var ProductOptionRepositoryInterface
     */
    protected $optionsRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     *
     * @var Registry
     */
    protected $registry;

    /**
     * @var UrlInferface
     */
    protected $urlBuilder;

    /**
     * @var MatboardTopInteger
     */
    protected $matboardTopInteger;

    /**
     * @var MatBoardTopFraction
     */
    protected $matboardTopFraction;

    /**
     * @var Data
     */
    protected $customizerHelper;

    /**
     * @var Mat
     */
    protected $matHelper;

    protected $customizerConfig = null;
    protected $formKey;
    protected $listBlock;
    protected $checkoutHelper;
    protected $quoteHelper;

    protected $fromData = null;
    protected $quoteItemFactory;
    protected $itemResourceModel;
    protected $quoteData;
    protected $sku = null;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @param ProductOptionRepositoryInterface $optionsRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Registry $registry
     */
    public function __construct(
        ProductOptionRepositoryInterface $optionsRepository,
        ProductRepositoryInterface $productRepository,
        Registry $registry,
        UrlInterface $urlBuilder,
        MatboardTopInteger $matboardTopInteger,
        MatboardTopFraction $matboardTopFraction,
        Data $customizerHelper,
        Mat $matHelper,
        FormKey $formKey,
        ListProduct $listBlock,
        Cart $checkoutHelper,
        QuoteCart $quoteHelper,
        ItemFactory $quoteItemFactory,
        Item $itemResourceModel,
        QuoteHelper $quoteData,
        Http $request,
    ) {
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->urlBuilder = $urlBuilder;
        $this->matboardTopInteger = $matboardTopInteger;
        $this->matboardTopFraction = $matboardTopFraction;
        $this->customizerHelper = $customizerHelper;
        $this->matHelper = $matHelper;
        $this->formKey = $formKey;
        $this->listBlock = $listBlock;
        $this->checkoutHelper = $checkoutHelper;
        $this->quoteHelper = $quoteHelper;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->itemResourceModel = $itemResourceModel;
        $this->quoteData = $quoteData;
        $this->request = $request;
    }

    /**
     * To get progress bar data
     *
     * @return array
     */
    public function getOptionGroupItems()
    {
        $options = [];
        $product =  $this->getProduct();
        $visibleProducts = $this->optionsRepository->getList($product->getSku(), "primary");
        return $visibleProducts;
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
     * @param $config
     * @return void
     */
    public function setConfig($config)
    {
        $this->customizerConfig = $config;
    }

    /**
     * Get product
     *
     * @return string
     */
    public function getSku()
    {
        if ($this->sku === null) {
            return $this->registry->registry('current_product')->getSku();
        }
        return $this->sku;
    }

    /**
     * @param $data
     * @return void
     */
    public function setFromData($data)
    {
        $this->fromData = $data;
    }

    /**
     * Return configuration array
     *
     * @return array|mixed
     */
    public function getConfig()
    {
//        $quoteItemId = null;
//        $requestUri = $_SERVER['REQUEST_URI'];
//        $url = explode("/", $requestUri);
//
//        $uriPath = explode('/', $this->request->getUri()->getPath());
//        if (isset($uriPath[1])) {
//            $path = $this->request->getParam('selection') !== null ?
//                $this->request->getParam('selection') :
//                $uriPath[1];
//            switch ($path) {
//                case 'checkout':
//                    $quoteItemId = $this->request->getParam('item_id');
//                    break;
//                case 'request_quote':
//                    $quoteItemId = $this->request->getParam('item_id');
//                    break;
//                case 'saved_designs':
//                    $quoteItemId = $this->request->getParam('item_id');
//                    break;
//            }
//        }
        $product = $this->getProduct();
////        $config['src_type'] = 'default';
//        if($quoteItemId) {
//            $config['quote']['item_id'] = $quoteItemId;
//            $quoteItem = $this->quoteItemFactory->create();
//            $this->itemResourceModel->load($quoteItem, $quoteItemId);
//            $config['quote']['qty'] = $quoteItem->getId() ? intval($quoteItem->getQty()):1;
//        }
        $config['options'] = $this->getOptionsConfig();
        $config['optionItemsAjaxUrl'] = $this->getOptionItemsAjaxUrl();
        $config['productName'] = $product->getName();
        $config['productId'] = $product->getId();
        $config['addToCartUrl'] =  $this->checkoutHelper->getAddUrl($product, []);
        $config['addToQuoteUrl'] =  $this->quoteHelper->getAddUrl($product, []);
        $config['form_key'] = $this->getFormKey();
        $config['productSku'] = $product->getSku();
        $config['matSizeConfig'] = $this->getMatOptionsConfig();
        $config['quote_active'] = $this->isRequestQuoteAllowed();
        return $config;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function isRequestQuoteAllowed()
    {
       return ($this->quoteData->isActive()
           && $this->quoteData->displayByuButtonOnPdp());
    }

    /**
     * @return UrlInterface
     */
    public function getOptionItemsAjaxUrl()
    {
        return $this->urlBuilder->getUrl('customizer/option/getitems');
    }

    /**
     * @return array
     */
    public function getOptionsConfig()
    {
        $options = [];
        $defaultSelection = [];
        //array_push($options, $this->getSizeOption());
        foreach ($this->getOptionGroupItems() as $item) {
            $option = [];
            $index = 2;
            $option['title'] = $item->getTitle();
            $option['position'] = $item->getPosition();
            $option['index'] = $index;
            // $option['default_title'] = $item->getDefaultTitle();
            // $option['sku'] = $item->getSku();
            $option['option_id'] = $item->getOptionId();
            $option['is_visible'] = false;
            $option['is_completed'] = false;
            $option['total_products'] = count($item->getProductLinks());
            $product = $this->getProduct();
            $selections = $product->getTypeInstance()->getSelectionsCollection([$item->getOptionId()], $product);
            if ($selections) {
                foreach ($selections as $selection) {
                    if ($selection->getIsDefault()) {
                        $defaultSelection['selection_id'] = $selection->getId();
                        $defaultSelection['selection_name'] = $selection->getName();
                        $defaultSelection['product_id'] = $selection->getEntityId();
                        $defaultSelection['position'] = $selection->getPosition();
                        $defaultSelection['qty'] = $selection->getQty();
                        $defaultSelection['price'] = $selection->getPrice();
                        $defaultSelection['swatch_image'] = $selection->getSwatchImage();
                        $defaultSelection['option_name'] = $item->getTitle();
                    }
                }
            }
            $option['default_selection'] = $defaultSelection;
            //array_push($options, $option);
            $code = $this->customizerHelper->getCode($item->getTitle());
            $options[$code] = $option;
            $index += 1;
        }
        //return $options;
    }

    public function getMatOptionsConfig()
    {
        $options = [];
        $options['matInteger'] =  $this->matboardTopInteger->getAllOptions();
        $options['matFraction'] = $this->matboardTopFraction->getAllOptions();
        $options['reveals']  = $this->matHelper->getRevealsOptions();
        return $options;
    }

}
