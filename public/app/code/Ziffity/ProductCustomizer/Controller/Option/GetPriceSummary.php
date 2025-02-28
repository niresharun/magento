<?php

namespace Ziffity\ProductCustomizer\Controller\Option;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Ziffity\ProductCustomizer\Model\SizeOptionConfigProvider;
use Ziffity\ProductCustomizer\Model\Components\Measurements\FrameSize;

class GetPriceSummary implements ActionInterface
{

    protected $request;

    protected $jsonFactory;

    protected $sizeOptionProvider;

    protected $frameSize;

    public function __construct(
        JsonFactory              $jsonFactory,
        RequestInterface         $request,
        SizeOptionConfigProvider $sizeOptionProvider,
        FrameSize                $frameSize
    )
    {
        $this->jsonFactory = $jsonFactory;
        $this->request = $request;
        $this->sizeOptionProvider = $sizeOptionProvider;
        $this->frameSize = $frameSize;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $pricingData = $this->request->getParams();
        $prices = [];
        $label = '';
        try {
            if($pricingData['data']){
                foreach ($pricingData['data'] as $key => $value){
                    switch($key){
                        case 'frame':
                            $label = 'Frame';
                            break;
                        case 'mat':
                            $label = 'Mat';
                            break;
                        case 'cork_board':
                            $label = 'Cork Board';
                            break;
                        case 'dryerase_board':
                            $label = 'Dry Erase Board';
                            break;
                        case 'chalk_board':
                            $label = 'Chalk Board';
                            break;
                        case 'glass':
                            $label = 'Glass/Glazing';
                            break;
                        case 'post_finish':
                            $label = 'Post Finish';
                            break;
                        case 'fabric':
                            $label = 'Fabric';
                            break;
                        default:
                            $label = 'No Label';
                            break;
                    }
                    if(is_numeric($value)){
                        $value = '$'.$value;
                    }
                    $prices[$label] = $value;
                }
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
            return $result->setData($prices);
        } catch (\Exception $exception) {
            return $result;
        }
    }
}
