<?php
declare(strict_types=1);

namespace Ziffity\Checkout\ViewModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Address\Config;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Helper\Reorder;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;


class SuccessPage implements ArgumentInterface
{
    protected $checkoutSession;

    protected $addressCollection;

    protected $orderRepository;

    protected $addressConfig;

    protected $addressMapper;

    protected $render;

    protected $imageHelper;

    protected $productRepository;

    protected $priceHelper;

    protected $customerSession;

    protected $date;

    protected $reorder;

    protected $postHelper;

    protected $addressRepository;

    protected $storeManager;


    /**
     * @param Session $checkoutSession
     */
    public function __construct(
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        CollectionFactory $addressCollection,
        OrderRepositoryInterface $orderRepository,
        Config $addressConfig,
        Renderer $render,
        Image $imageHelper,
        ProductRepositoryInterface $productRepository,
        TimezoneInterface $date,
        Data $priceHelper,
        Mapper $addressMapper,
        CustomerSession $customerSession,
        Reorder $reorder,
        PostHelper $postHelper,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->addressCollection = $addressCollection;
        $this->orderRepository = $orderRepository;
        $this->addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
        $this->render = $render;
        $this->imageHelper = $imageHelper;
        $this->productRepository = $productRepository;
        $this->priceHelper = $priceHelper;
        $this->date =  $date;
        $this->customerSession = $customerSession;
        $this->reorder = $reorder;
        $this->postHelper = $postHelper;
        $this->addressRepository = $addressRepository;
    }

    /**
     * @return CustomerSession
     */
    public function getCustomer()
    {
        return $this->customerSession;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * @throws LocalizedException
     */
    public function getOrderItems()
    {
        $order = $this->getOrder();
        try {
            $order = $this->orderRepository->get($order->getId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('This order no longer exists.'));
        }
        return $order;
    }

    /**
     * @param $orderId
     * @return string|null
     */
    public function getOrderCreatedAt($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        /** @var TYPE_NAME $createdAt */
        $createdAt = $this->getTimeconverter($order->getCreatedAt());
        return $createdAt;
    }

    /**
     * Format Shipping Address
     * @return string
     */
    public function formatShipping()
    {
        $order = $this->getOrder();
        if ($order->getShippingAddress()) {
            return $this->getFormattedAddress($order->getShippingAddress());
        }
        return false;
    }

    public function getFormattedAddress($address)
    {
        return $this->render->format($address, 'html');
    }

    /**
     * @return false|string|null
     */
    public function formatBilling()
    {
        $order = $this->getOrder();
        if ($order->getBillingAddress()) {
            return $this->getFormattedAddress($order->getBillingAddress());
        }
        return false;
    }

    /**
     * @param int $id
     * @return string
     */
    public function getItemImage($productId)
    {
        try {
            $_product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            return 'product not found';
        }
        return $this->imageHelper->init($_product, 'product_base_image')->getUrl();
    }

    /**
     * @param $price
     * @return float|string
     */
    public function getFormattedPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }

    /**
     * @param $date
     * @return string
     */
    public function getTimeconverter($date)
    {
        return $this->date->date($date)->format('m/d/Y');
    }

    /**
     * @return Reorder
     */
    public function getReorder()
    {
        return $this->reorder;
    }

    /**
     * @return PostHelper
     */
    public function getPostHelper()
    {
        return $this->postHelper;
    }
    public function getProductImage($productId)
    {
        $product = $this->productRepository->getById($productId);
        $store = $this->storeManager->getStore();
        $productImage =  $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
        return $productImage;
    }
    /**
     * @return string | bool
     */
    public function getCustomerShipTo()
    {
        $order = $this->getOrder();
        $shipTo = '';
        if ($order->getShippingAddress()) {
            if ($order->getShippingAddress()->getCustomerAddressId()) {
                $addressId = $order->getShippingAddress()->getCustomerAddressId();
                $addressData = $this->addressRepository->getById($addressId);
                if(null !== $addressData->getCustomAttribute("ship_to")) {
                    $shipTo = $addressData->getCustomAttribute("ship_to")->getValue();
                }
            }
        }
        
        return $shipTo;
    }
}
