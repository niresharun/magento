<?php

namespace Ziffity\DigitalSigning\Model;

use Magento\Framework\Model\AbstractModel;

class ImageData extends AbstractModel
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Ziffity\DigitalSigning\Model\ResourceModel\ImageData::class);
    }
}
