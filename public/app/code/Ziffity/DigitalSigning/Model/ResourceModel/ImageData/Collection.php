<?php

namespace Ziffity\DigitalSigning\Model\ResourceModel\ImageData;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Ziffity\DigitalSigning\Model\ImageData::class, \Ziffity\DigitalSigning\Model\ResourceModel\ImageData::class);
    }
}
