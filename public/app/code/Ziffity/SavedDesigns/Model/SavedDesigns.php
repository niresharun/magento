<?php

namespace Ziffity\SavedDesigns\Model;

use Magento\Framework\Model\AbstractModel;

class SavedDesigns extends AbstractModel
{

    const CACHE_TAG = 'ziffity_saved_designs';

    const IMAGE_PATH = 'catalog/product/canvas/';

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns');
    }
}
