<?php

namespace Ziffity\Shipping\Model\ShippingProfile\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ShippingProfile extends AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('shipping_profile', 'profile_id');
    }
}
