<?php

namespace Ziffity\ProductCustomizer\Controller\Option;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Ziffity\CustomFrame\Helper\Data;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ProductRepository;

class GetLabelData implements ActionInterface
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
    protected $labels;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @param RequestInterface $request
     * @param Data $labels
     * @param JsonFactory $jsonFactory
     * @param ProductRepository $productRepository
     * @return void
     */
    public function __construct(
        RequestInterface $request,
        Data $labels,
        JsonFactory $jsonFactory,
        ProductRepository $productRepository
    )
    {
        $this->request = $request;
        $this->labels = $labels;
        $this->jsonFactory = $jsonFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            $labelData = [];
            $param = $this->request->getParam('data');
            if ($param) {
                $product = $this->productRepository->get($param);
                $labelData = $this->labels->getLabelsJson($product);
            }
            return $this->jsonFactory->create()->setData([
                'success' => !empty($labelData),
                'label_data' => $labelData
            ]);
        }catch (\Exception $exception){
            return $this->jsonFactory->create()->setData(['success'=>false]);
        }
    }
}
