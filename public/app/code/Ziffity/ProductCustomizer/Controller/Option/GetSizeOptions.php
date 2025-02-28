<?php

namespace Ziffity\ProductCustomizer\Controller\Option;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Ziffity\ProductCustomizer\Model\SizeOptionConfigProvider;

class GetSizeOptions implements ActionInterface
{

    protected $request;

    protected $jsonFactory;

    protected $sizeOptionProvider;

    public function __construct(JsonFactory $jsonFactory,RequestInterface $request,
    SizeOptionConfigProvider $sizeOptionProvider){
        $this->jsonFactory = $jsonFactory;
        $this->request = $request;
        $this->sizeOptionProvider = $sizeOptionProvider;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $sizeItems['size_option'] = [];
        try {
            $productSku = $this->request->getParam('product_sku');
            if ($productSku){
                $this->sizeOptionProvider->setSku($productSku);
                $sizeItems = $this->sizeOptionProvider->getConfig();
            }
            return $result->setData($sizeItems);
        }catch (\Exception $exception){
            return $result;
        }
    }
}
