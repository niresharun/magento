<?php

namespace Ziffity\ProductCustomizer\Controller\Option;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Ziffity\CustomFrame\Helper\Data;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ProductRepository;

class GetHeaderData implements ActionInterface
{

    /**
     * @var RequestInterface
     */
    public $request;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var Data
     */
    protected $headers;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @param RequestInterface $request
     * @param Data $headers
     * @param JsonFactory $jsonFactory
     * @param ProductRepository $productRepository
     * @return void
     */
    public function __construct(
    RequestInterface $request,
    Data $headers,
    JsonFactory $jsonFactory,
    ProductRepository $productRepository
    )
    {
        $this->request = $request;
        $this->headers = $headers;
        $this->jsonFactory = $jsonFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            $headerData = [];
            $param = $this->request->getParam('data');
            if ($param) {
                $product = $this->productRepository->get($param);
                $headerData = $this->headers->getHeadersJson($product);
            }
            return $this->jsonFactory->create()->setData([
                'success' => !empty($headerData),
                'header_data' => $headerData
            ]);
        }catch (\Exception $exception){
            return $this->jsonFactory->create()->setData(['success'=>false]);
        }
    }
}
