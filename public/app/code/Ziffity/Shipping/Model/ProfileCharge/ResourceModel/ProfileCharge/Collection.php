<?php

namespace Ziffity\Shipping\Model\ProfileCharge\ResourceModel\ProfileCharge;

use Ziffity\Shipping\Model\ProfileCharge\ProfileCharge;
use Ziffity\Shipping\Model\ProfileCharge\ResourceModel\ProfileCharge as ResourceModel;
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
        $this->_init(ProfileCharge::class, ResourceModel::class);
    }
}
