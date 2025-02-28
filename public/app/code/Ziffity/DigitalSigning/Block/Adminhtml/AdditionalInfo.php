<?php

namespace Ziffity\DigitalSigning\Block\Adminhtml;

use Magento\Sales\Model\OrderRepository;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\Request\Http;

class AdditionalInfo extends Template
{
    /**
     * @var Http
     */
    protected $request;
    /**
     * @var OrderRepository
     */
    protected $orderRepository;


    /**
     * @param Context $context
     * @param Http $request
     * @param OrderRepository $orderRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Http $request,
        OrderRepository $orderRepository,
        array $data = []
    ) {
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
    }

    /**
     * Getting Data Using CollectionFactory
     */

    public function getOrder()
    {
        $orderId = $this->request->getParam('order_id');
        return $this->orderRepository->get($orderId);
    }
}
