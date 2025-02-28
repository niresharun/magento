<?php

namespace Ziffity\ProductCustomizer\Controller\Option;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;

class GetItems extends \Magento\Framework\App\Action\Action
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
        Context  $context
    ) {

        $this->resultJsonFactory = $resultJsonFactory;
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    public function execute() 
    {
        $post = $this->getRequest()->getPost();
        if ($post['optionId'] && $post['sku']) {
           $option = $this->optionsRepository->get($post['sku'], $post['optionId']);
        }

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();
        return $result->setData(['success' => true]);
    } 
}
