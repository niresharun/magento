<?php

namespace Ziffity\ProductCustomizer\Controller\Option;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use mysql_xdevapi\Exception;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Ziffity\ProductCustomizer\Model\Components\Measurements\FrameSize;
use Ziffity\ProductCustomizer\Helper\Data;
use Ziffity\ProductCustomizer\Helper\Selections;

class GetYourSelections extends \Magento\Framework\App\Action\Action
{

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ProductOptionRepositoryInterface
     */
    protected $optionsRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    protected $imageHelper;

    protected $storeManager;

    protected $frameSize;

    protected $helper;

    protected $selectionsHelper;

    /**
     * @param Context
     * @param JsonFactory $resultJsonFactory
     * @param ProductOptionRepositoryInterface $optionsRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        ProductOptionRepositoryInterface $optionsRepository,
        ProductRepositoryInterface $productRepository,
        Image $imageHelper,
        StoreManagerInterface $storeManager,
        FrameSize $frameSize,
        Data $helper,
        Selections $selectionsHelper,
        Context  $context
    ) {

        $this->resultJsonFactory = $resultJsonFactory;
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->storeManager = $storeManager;
        $this->frameSize = $frameSize;
        $this->helper = $helper;
        $this->selectionsHelper = $selectionsHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $details = '';
        $completedSteps = '';
        $post = $this->getRequest()->getParam('data');
        $attributes = [];
        $product = $this->productRepository->get($post['sku']);
        $options = $post['options'];
        $completedSteps = isset($post['completedSteps']) ? $post['completedSteps'] : $this->selectionsHelper->getCompletedStepsFromOptions(json_encode($options));
        if(!empty($completedSteps)) {
            $attributes = $this->selectionsHelper->getSelections($options, $product, $completedSteps);
        }
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();
        return $result->setData($attributes);

    }



}
