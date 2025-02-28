<?php

namespace Ziffity\Shipping\Model\ProfileCharge\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProfileCharge extends AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('shipping_profile_charge', 'charge_id');
    }
}
