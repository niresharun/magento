<?php

namespace Ziffity\Shipping\Model\OversizeProfile\ResourceModel\OversizeProfile;

use Ziffity\Shipping\Model\OversizeProfile\ResourceModel\OversizeProfile as ResourceModel;
use Ziffity\Shipping\Model\OversizeProfile\OversizeProfile as Model;
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
