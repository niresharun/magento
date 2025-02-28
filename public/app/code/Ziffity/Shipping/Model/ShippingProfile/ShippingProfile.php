<?php

namespace Ziffity\Shipping\Model\ShippingProfile;

use Magento\Framework\Model\AbstractExtensibleModel;
use Ziffity\Shipping\Model\ShippingProfile\ResourceModel\ShippingProfile as ResourceModel;

class ShippingProfile extends AbstractExtensibleModel
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
