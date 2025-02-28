<?php

namespace Ziffity\SavedDesigns\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Ziffity\SavedDesigns\Helper\Data;

class SavedDesigns extends AbstractDb
{

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param Context $context
     * @param Data $helperData
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        Data    $helperData,
        $connectionName = null
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $connectionName);
    }

    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('ziffity_saved_designs', 'entity_id');
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getId()) {
            $customerId = (int)$object->getCustomerId();
            $connection = $this->getConnection();
            $select = $connection->select();
            $select->from($this->getMainTable(), 'COUNT(*)')->where('customer_id = ' . $customerId);
            $currentCount = $connection->fetchOne($select);

            if ($currentCount >= $this->helperData->getSaveLimitScope()) {
                throw new LocalizedException(__('You have reached the maximum limit of saving design. Please remove unwanted saved designs to save new designs.'));
            }
            $object->setShareCode(uniqid());
        }
        return $this;
    }
}
