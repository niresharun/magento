<?php

namespace Ziffity\ProductCustomizer\Controller\Option;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Ziffity\ProductCustomizer\Model\FrameOptionConfigProvider;
use Magento\Framework\App\RequestInterface;

class GetFrameItems implements ActionInterface
{

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var FrameOptionConfigProvider
     */
    protected $frameOptionProvider;

    /**
     * @param JsonFactory $jsonFactory
     * @param FrameOptionConfigProvider $frameOptionProvider
     * @param RequestInterface $request
     */
    public function __construct(
        JsonFactory               $jsonFactory,
        FrameOptionConfigProvider $frameOptionProvider,
        RequestInterface          $request
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->frameOptionProvider = $frameOptionProvider;
        $this->request = $request;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface|void
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();
        $productItems['products'] = [];
        try {
            $productSku = $this->request->getParam('product_sku');
            if ($productSku) {
                $this->frameOptionProvider->setSku($productSku);
                $productItems = $this->frameOptionProvider->getConfig();
            }
            return $result->setData($productItems);
        } catch (\Exception $exception) {
            return $result;
        }
    }
}
