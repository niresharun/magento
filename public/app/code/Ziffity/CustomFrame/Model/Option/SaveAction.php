<?php

declare(strict_types=1);

namespace Ziffity\CustomFrame\Model\Option;


use Magento\Bundle\Model\ResourceModel\Option;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Bundle\Model\Product\Type;
use Ziffity\CustomFrame\Api\ProductLinkManagementInterface;
use Magento\Store\Model\StoreManagerInterface;

class SaveAction extends \Magento\Bundle\Model\Option\SaveAction 
{
    /**
     * @param Option $optionResource
     * @param MetadataPool $metadataPool
     * @param Type $type
     * @param ProductLinkManagementInterface $linkManagement
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        Option $optionResource,
        MetadataPool $metadataPool,
        Type $type,
        ProductLinkManagementInterface $linkManagement,
        ?StoreManagerInterface $storeManager = null
    ) {
        parent::__construct($optionResource, $metadataPool, $type, $linkManagement, $storeManager);
    }
}
