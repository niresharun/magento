<?php
  
namespace Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns;
  
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
  
class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Ziffity\SavedDesigns\Model\SavedDesigns',
            'Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns'
        );
    }
}