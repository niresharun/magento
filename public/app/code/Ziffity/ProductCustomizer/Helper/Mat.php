<?php

namespace Ziffity\ProductCustomizer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;

class Mat extends AbstractHelper
{

    /**
     * @var ProductOptionRepositoryInterface
     */
    protected $optionsRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    protected $_floatToFractionalHash = [];

    /**
     *
     * @var Registry
     */
    protected $registry;

    /**
     * @param ProductOptionRepositoryInterface $optionsRepository
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Context $context,
        ProductOptionRepositoryInterface $optionsRepository,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Registry $registry
    ) {
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * @return
     */
    public function getRevealsOptions()
    {
        $reveals = [
            '1/4"'  => '0.25',
            '3/8"'  => '0.375',
            '1/2"'  => '0.5',
            '5/8"'  => '0.625',
            '3/4"'  => '0.75',
            '7/8"'  => '0.875'
        ];
        return $reveals;
    }

    /**
     * @return mixed
     */
    public function getAllIdsSorted($collection)
    {
        $ids = $collection->getSelect()->columns($collection->getIdFieldName());
        //clog('collection', json_encode($collection->getSelect()->__toString()));
        $ids = $collection->getConnection()->fetchCol($collection->getSelect());

        return $ids;
    }
}
