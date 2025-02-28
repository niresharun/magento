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

/**
 * CheckoutCartAddAfter
 */
class CheckoutCartAddAfter implements ObserverInterface
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

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @param StoreManagerInterface $storeManager
     * @param LayoutInterface $layout
     * @param RequestInterface $request
     * @param Json $serializer
     * @param Data $helper
     * @param FormKey $formKey
     * @param Factory $dataObject
     * @param ProductRepositoryInterface $productRepository
     * @param SessionFactory $checkoutSession
     * @param CartRepositoryInterface $cartRepository
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
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository
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
    }

    /**
     * @param EventObserver $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $postValue = $this->_request->getParams();
        if(isset($postValue['accessories_items'])) {
            $accessoriesItems = $postValue['accessories_items'];
            $this->addToCart($accessoriesItems);
        }
    }

    /**
     * @param $items
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addToCart($items)
    {
        foreach ($items as $item) {
            $product = $this->productRepository->getById($item['id']);
            $params = ['form_key' => $this->formKey->getFormKey(),
                'product' => $item['id'], 'qty' => 1, 'price'=>$item['price'],
                'item'=>1];
            $buyRequest = $this->dataObject->create($params);
            $session = $this->checkoutSession->create();
            $quote = $session->getQuote();
            $quote->addProduct($product, $buyRequest);
            $this->cartRepository->save($quote);
            $session->replaceQuote($quote)->unsLastRealOrderId();
        }
    }

}
