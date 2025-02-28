<?php

namespace Ziffity\ProductCustomizer\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\Serialize\Serializer\Json;
use \Magento\Framework\App\RequestInterface;
use Ziffity\CustomFrame\Model\Product\Price;

/**
 * CheckoutCartAddObserver
 */
class CheckoutCartAddBefore implements ObserverInterface
{

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var RequestInterface
     */
    protected $_request;
    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var Price
     */
    protected $priceModel;

    /**
     * @param RequestInterface $request
     * @param Json $serializer
     * @param ProductRepositoryInterface $productRepository
     * @param Price $priceModel
     */
    public function __construct(
        RequestInterface $request,
        Json $serializer,
        ProductRepositoryInterface $productRepository,
        Price $priceModel
    ) {
        $this->_request = $request;
        $this->serializer = $serializer;
        $this->productRepository = $productRepository;
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
        $eventName = $observer->getEvent()->getName();
        $postValue = $observer->getInfo();
        if (isset($postValue['options']) &&
            $postValue['options']) {
            if($eventName == "checkout_cart_product_add_before" &&
                isset($postValue['options']['accessories']['active_items'])) {
                $accessoriesItems = $this->getAccessoriesItems($postValue);
                $originalData = $this->removeDuplicateCanvas($postValue['options']);
                $postValue['options']['additional_data']['original_additional_data'] = $this->serializer->
                    serialize($originalData);
                unset($postValue['options']['accessories']);
                $postValue = $this->recalculateSubtotal($postValue);
                $observer->setInfo($postValue);
                $postValue['accessories_items'] = $accessoriesItems;
                $this->_request->setParams($postValue);
            }
        }
    }

    public function recalculateSubtotal($postValue)
    {
        try {
            $product = $this->productRepository->getById($postValue['product']);
        }catch (NoSuchEntityException $exception){
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Product that you are trying to add is not available.')
            );
        }
        $price = $this->priceModel->getPriceSummary($product, $postValue['options']);
        $postValue['options']['additional_data']['subtotal'] = $price['subtotal'];
        return $postValue;
    }

    public function removeDuplicateCanvas($data)
    {
        if (isset($data['additional_data']['canvasData'])){
            unset($data['additional_data']['canvasData']);
            return $data;
        }
        return $data;
    }

    public function getAccessoriesItems($data)
    {
        $result  = [];
        foreach ($data['options']['accessories']['active_items'] as $key=>$item) {
            $result[$key] = $item;
        }
        return $result;
    }

}
