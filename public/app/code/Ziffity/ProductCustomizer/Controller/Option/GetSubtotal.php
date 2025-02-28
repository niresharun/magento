<?php

namespace Ziffity\ProductCustomizer\Controller\Option;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Ziffity\ProductCustomizer\Model\SizeOptionConfigProvider;
use Ziffity\ProductCustomizer\Model\Components\Measurements\FrameSize;
use Ziffity\CustomFrame\Model\Product\Price;
use Magento\Catalog\Model\ProductRepository;

class GetSubtotal implements ActionInterface
{

    protected $request;

    protected $jsonFactory;

    protected $sizeOptionProvider;

    protected $frameSize;

    protected $priceModel;

    protected $productRepository;

    public function __construct(
        JsonFactory $jsonFactory,
        RequestInterface $request,
        SizeOptionConfigProvider $sizeOptionProvider,
        FrameSize $frameSize,
        Price $priceModel,
        ProductRepository $productRepository
    )
    {
        $this->jsonFactory = $jsonFactory;
        $this->request = $request;
        $this->sizeOptionProvider = $sizeOptionProvider;
        $this->frameSize = $frameSize;
        $this->priceModel = $priceModel;
        $this->productRepository = $productRepository;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $pricingData = $this->request->getParams();
        $price = '';
        $label = '';
        try {
            if($pricingData['data']){
                $product = $this->productRepository->get($pricingData['data']['sku']);
                $price = $this->priceModel->getPriceSummary($product, $pricingData['data']['options']);
            }

//            $overallFrameWidth = $this->frameSize->getOverallWidth($customizerData['data']);
//            $overallFrameHeight = $this->frameSize->getOverallHeight($customizerData['data']);
//
//            $attributes['overall_frame_size'] = [
//                'label' => __('Overall Frame Size'),
//                'value' => trim($overallFrameWidth) . '&quot; by ' . trim($overallFrameHeight) . '&quot;'
//            ];
//            $overallFrameWidth = $this->frameSize->getInnerFrameWidth($customizerData['data']);
//            $overallFrameHeight = $this->frameSize->getInnerFrameHeight($customizerData['data']);
//            $attributes['inner_frame_size'] = [
//                'label' => __('Inner Frame Size'),
//                'value' => trim($overallFrameWidth) . '&quot; by ' . trim($overallFrameHeight) . '&quot;'
//            ];
            return $result->setData($price);
        } catch (\Exception $exception) {
            return $result;
        }
    }
}
