<?php

namespace Ziffity\Shipping\Model\ShippingProfile\ResourceModel\ShippingProfile;

use Ziffity\Shipping\Model\ShippingProfile\ResourceModel\ShippingProfile as ResourceModel;
use Ziffity\Shipping\Model\ShippingProfile\ShippingProfile as Model;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Model initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
