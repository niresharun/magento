<?php

namespace Ziffity\Shipping\Model\OversizeProfile;

use Magento\Framework\Model\AbstractExtensibleModel;
use Ziffity\Shipping\Model\OversizeProfile\ResourceModel\OversizeProfile as ResourceModel;

class OversizeProfile extends AbstractExtensibleModel
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
