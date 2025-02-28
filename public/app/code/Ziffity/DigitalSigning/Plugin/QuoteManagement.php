<?php
namespace Ziffity\DigitalSigning\Plugin;

use Magento\Sales\Model\OrderRepository;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Checkout\Model\Session;
use Ziffity\DigitalSigning\Model\ImageDataFactory;

class QuoteManagement
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected \Magento\Framework\Webapi\Rest\Request $request,
        protected Session $checkoutSession,
        protected ImageDataFactory $dataFactory
    ) {
    }

    public function aroundPlaceOrder(
        \Magento\Quote\Model\QuoteManagement $subject,
        callable $proceed,
        $cartId,
        PaymentInterface $paymentMethod = null,
    ) {
        $imagePath = $purchaseData = $orderNotes = null;
        if  (array_key_exists('ImageData', $this->request->getBodyParams())) {
            $imagePath = $this->request->getBodyParams()['ImageData'];
        }
        if (array_key_exists('purchaseData', $this->request->getBodyParams())) {
            $purchaseData = $this->request->getBodyParams()['purchaseData'];
        }
        if (array_key_exists('orderNotes', $this->request->getBodyParams())) {
            $orderNotes = $this->request->getBodyParams()['orderNotes'];
        }
        $result = $proceed($cartId, $paymentMethod);
        $orderId = $this->checkoutSession->getData('last_order_id');
        $model = $this->dataFactory->create();
        $sampleData = [
            "parent_type" => 'quote',
            "parent_id" => $orderId,
            "digital_signature" => $imagePath,
        ];
        $model->setData($sampleData)->save();
        $salesModel = $this->orderRepository->get($orderId);
        $salesModel->setData("purchase_order", $purchaseData);
        $salesModel->setData("order_notes", $orderNotes);
        $this->orderRepository->save($salesModel);
        return $result;
    }
}
