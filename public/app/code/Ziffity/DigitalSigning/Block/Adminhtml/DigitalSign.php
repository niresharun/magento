<?php

namespace Ziffity\DigitalSigning\Block\Adminhtml;

use Ziffity\DigitalSigning\Model\ImageDataFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\Request\Http;

class DigitalSign extends Template
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var ImageDataFactory
     */

    protected $modelDataFactory;

    /**
     * @param Context $context
     * @param Http $request
     * @param ImageDataFactory $modelDataFactory
     * @param array $data
     */

    public function __construct(
        Context $context,
        Http $request,
        ImageDataFactory $modelDataFactory,
        array $data = []
    ) {
        $this->request = $request;
        $this->modelDataFactory = $modelDataFactory;
        parent::__construct($context, $data);
    }

    /**
     * Getting Data Using ModelFactory
     */

    public function getOrder()
    {
        $orderId = $this->request->getParam('order_id');
        return $this->modelDataFactory->create()->load($orderId, "parent_id");
    }
}
