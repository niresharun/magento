<?php

namespace Ziffity\Shipping\Model\OversizeProfileCharge\ResourceModel;

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
        $this->_init('oversize_profile_charge', 'charge_id');
    }
}
