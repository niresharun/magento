<?php

namespace Ziffity\Shipping\Model\OversizeProfile\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OversizeProfile extends AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('oversize_profile', 'profile_id');
    }
}
