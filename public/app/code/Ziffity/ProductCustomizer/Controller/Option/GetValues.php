<?php

namespace Ziffity\ProductCustomizer\Controller\Option;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;

class GetValues implements \Magento\Framework\App\ActionInterface
{

    /**
     * @var RequestInterface
     */
    public $request;

    /**
     * @var JsonFactory
     */
    public $jsonFactory;

    /**
     * @var array|mixed
     */
    public $configProvider;

    /**
     * @param JsonFactory $jsonFactory
     * @param RequestInterface $request
     * @param $configProvider
     */
    public function __construct(
        JsonFactory  $jsonFactory,
        RequestInterface  $request,
        $configProvider = []
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->request = $request;
        $this->configProvider = $configProvider;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        try {
            $result = $this->jsonFactory->create();
            $params = $this->request->getParam('data');
            $resultDatum = [];
            if (isset($params['option']) &&
                isset($this->configProvider[$params['option']])) {
                $resultDatum = $this->configProvider[$params['option']]
                    ->getItems($params);
            }
            $result->setData($resultDatum);
            return $result;
        } catch (\Exception $exception) {
            return $this->jsonFactory->create()->setData(['error'=>true,
                'error_message'=>$exception->getMessage()]);
        }
    }
}
