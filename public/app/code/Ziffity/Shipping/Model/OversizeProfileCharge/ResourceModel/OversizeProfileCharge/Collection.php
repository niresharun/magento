<?php

namespace Ziffity\Shipping\Model\OversizeProfileCharge\ResourceModel\OversizeProfileCharge;

use Ziffity\Shipping\Model\OversizeProfileCharge\ProfileCharge as Model;
use Ziffity\Shipping\Model\OversizeProfileCharge\ResourceModel\ProfileCharge as ResourceModel;
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
