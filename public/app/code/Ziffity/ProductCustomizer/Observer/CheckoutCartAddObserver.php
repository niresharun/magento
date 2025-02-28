<?php

namespace Ziffity\ProductCustomizer\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\SessionFactory;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use \Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\CartRepositoryInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\View\LayoutInterface;
use \Magento\Framework\App\RequestInterface;
use Ziffity\ProductCustomizer\Helper\Data;
use Magento\Framework\Data\Form\FormKey;
use Magento\Directory\Model\Currency;
use Ziffity\CustomFrame\Model\Product\Price;

/**
 * CheckoutCartAddObserver
 */
class CheckoutCartAddObserver implements ObserverInterface
{

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var LayoutInterface
     */
    protected $_layout;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var RequestInterface
     */
    protected $_request;
    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var Factory
     */
    protected $dataObject;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var SessionFactory
     */
    protected $checkoutSession;

    protected $currency;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    protected $priceModel;

    /**
     * @param StoreManagerInterface $storeManager
     * @param LayoutInterface $layout
     * @param RequestInterface $request
     * @param Json $serializer
     * @param Data $helper
     * @param FormKey $formKey
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        LayoutInterface $layout,
        RequestInterface $request,
        Json $serializer,
        Data $helper,
        FormKey $formKey,
        Factory $dataObject,
        ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Model\SessionFactory $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        Currency $currency,
        Price $priceModel,
    ) {
        $this->_layout = $layout;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->serializer = $serializer;
        $this->helper = $helper;
        $this->formKey = $formKey;
        $this->dataObject = $dataObject;
        $this->productRepository = $productRepository;
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->currency = $currency;
        $this->priceModel = $priceModel;
    }

    /**
     * execute
     *
     * @param EventObserver observer
     *
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $postValue = $this->_request->getParams();
        if (isset($postValue['options']) &&
            $postValue['options']) {
            $rowTotal = 0;
            $item = $observer->getQuoteItem() !== null ? $observer->getQuoteItem() : $observer->getItem();
            $product = $this->productRepository->get($item->getSku());
            $unitPrice = $this->priceModel->getPrice($product, $postValue['options']);
            $unitPrice =  preg_replace('/[^0-9-.]+/', '', $unitPrice);
            $item->setOriginalPrice($unitPrice);
            $item->setBaseOriginalPrice($unitPrice);
            $item->setBasePrice($unitPrice);
            $item->setPrice($unitPrice);
            $item->setRowTotal($unitPrice *  $item->getQty());
            $item->setBaseRowTotal($unitPrice *  $item->getQty());
            $item->setCustomPrice($unitPrice);
            $item->setOriginalCustomPrice($unitPrice);
            $item->getProduct()->setIsSuperMode(true);
            $postValue['options'] = isset($postValue['designId']) ? $postValue['options'] :
                $this->getCanvasImage($postValue['options']);
            $item->setAdditionalData($this->serializer->serialize($postValue['options']));
            $item->setCustomizerDetails($this->serializer->serialize($product->getCoProductDetails()));

            $product = $item->getProduct();

            $customOptions = $product->getCustomOptions();
            if (!empty($customOptions)) {
                foreach ($customOptions as $option) {
                    $option->setPrice($unitPrice);
                }
            }
            if($observer->getEvent()->getName() == 'checkout_cart_update_item_complete'){
                $item->save();
            }
        }
        // product_color_shade is input field in product view page
    }

    /**
     * This function gets the canvas image from the array and passed for saving.
     *
     * @param array $data
     * @return array
     * @throws AlreadyExistsException
     */
    public function getCanvasImage($data)
    {
        if (isset($data['additional_data']['canvasData']) && $data['additional_data']['canvasData']) {
            $result = $data['additional_data']['canvasData'];
            $fileName = $this->helper->generateImage($result);
            $data['additional_data']['canvasData'] = $fileName;
            return $data;
        }
        return $data;
    }
}
