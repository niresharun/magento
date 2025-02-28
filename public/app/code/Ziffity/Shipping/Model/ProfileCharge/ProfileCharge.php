<?php

namespace Ziffity\Shipping\Model\ProfileCharge;

use Magento\Framework\Model\AbstractExtensibleModel;
use Ziffity\Shipping\Model\ProfileCharge\ResourceModel\ProfileCharge as ResourceModel;

class ProfileCharge extends AbstractExtensibleModel
{
    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
