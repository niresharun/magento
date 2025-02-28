<?php

namespace Ziffity\ProductCustomizer\Controller\Option;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Ziffity\CustomFrame\Helper\Data;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ProductRepository;
use Ziffity\ProductCustomizer\Model\ImageConfigProvider;

class GetGalleryData implements ActionInterface
{

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ImageConfigProvider
     */
    protected $imageConfig;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @param RequestInterface $request
     * @param JsonFactory $jsonFactory
     * @param ImageConfigProvider $imageConfig
     */
    public function __construct(
        RequestInterface $request,
        JsonFactory $jsonFactory,
        ImageConfigProvider $imageConfig)
    {
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->imageConfig = $imageConfig;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $params = $this->request->getParam('data');
        $params['gallery_data'] = $this->imageConfig
            ->includeCanvasImage($params['gallery_data'],$params['canvas']);
        return $this->jsonFactory->create()->setData([
            'image'=> $this->imageConfig->buildGalleryScript($params),
            'video'=> $this->imageConfig->buildVideoGalleryScript($params),
        ]);
    }
}
