<?php

namespace Ziffity\RequestQuote\Model\ResourceModel;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\SalesSequence\Model\Manager;
use Amasty\RequestQuote\Model\Source\Status;


class Quote extends \Amasty\RequestQuote\Model\ResourceModel\Quote
{

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->saveAmastyQuote($object);
        return $this;
    }


    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     */
    private function saveAmastyQuote(\Magento\Framework\Model\AbstractModel $object)
    {
        $shippingCanModified = $object->hasData(QuoteInterface::SHIPPING_CAN_BE_MODIFIED)
            ? (int) $object->getData(QuoteInterface::SHIPPING_CAN_BE_MODIFIED)
            : 1;
        $isShippingConfigured = $object->hasData(QuoteInterface::SHIPPING_CONFIGURE)
            ? (int) $object->getData(QuoteInterface::SHIPPING_CONFIGURE)
            : 0;
        $archiveStatus = $object->getArchive() ? 1 : 0;

        $this->getConnection()->insertOnDuplicate($this->getAmastyQuoteTable(), [
            'quote_id'  => $object->getId(),
            'status'    => $object->getStatus(),
            'increment_id' => $object->prepareIncrementId(),
            'customer_name' => $object->prepareCustomerName(),
            'remarks' => $object->getRemarks(),
            'expired_date' => $object->getExpiredDate(),
            'reminder_date' => $object->getReminderDate(),
            'archive' => $archiveStatus,
            QuoteInterface::SUBMITED_DATE => $object->getData(QuoteInterface::SUBMITED_DATE),
            'quote_name' => $object->getData('quote_name'),
            QuoteInterface::ADMIN_NOTIFICATION_SEND => $object->getAdminNotificationSend(),
            QuoteInterface::DISCOUNT => $object->getData(QuoteInterface::DISCOUNT),
            QuoteInterface::SURCHARGE => $object->getData(QuoteInterface::SURCHARGE),
            QuoteInterface::REMINDER_SEND => $object->getData(QuoteInterface::REMINDER_SEND) ?: 0,
            QuoteInterface::SHIPPING_CAN_BE_MODIFIED => $shippingCanModified,
            QuoteInterface::SHIPPING_CONFIGURE => $isShippingConfigured,
            QuoteInterface::CUSTOM_FEE => (float) $object->getData(QuoteInterface::CUSTOM_FEE),
            QuoteInterface::CUSTOM_METHOD_ENABLED => (int) $object->getData(QuoteInterface::CUSTOM_METHOD_ENABLED),
            QuoteInterface::SUM_ORIGINAL_PRICE => $this->getSumOriginalPrice($object),
        ]);
    }

    /**
     * @param \Magento\Quote\Model\Quote $object
     * @return float
     */
    private function getSumOriginalPrice(\Magento\Framework\Model\AbstractModel $object): float
    {
        $origPrice= 0;
        foreach ($object->getAllVisibleItems() as $item) {
            if (!$item->isDeleted()) {
                $origPrice += $item->getBasePrice() * $item->getQty();
            }
        }

        return $origPrice;
    }

    /**
     * @param \Zend_Db_Select $select
     */
    private function joinAmastyQuote($select)
    {
        $select->joinInner(
            ['amquotes' => $this->getAmastyQuoteTable()],
            "amquotes.quote_id = " . $this->getMainTable() . ".entity_id",
            [
                'status',
                'remarks',
                'increment_id',
                'customer_name',
                'expired_date',
                'reminder_date',
                'submited_date',
                'archive',
                QuoteInterface::ADMIN_NOTIFICATION_SEND,
                QuoteInterface::SURCHARGE,
                QuoteInterface::DISCOUNT,
                QuoteInterface::REMINDER_SEND,
                QuoteInterface::SHIPPING_CAN_BE_MODIFIED,
                QuoteInterface::SHIPPING_CONFIGURE,
                QuoteInterface::CUSTOM_FEE,
                QuoteInterface::CUSTOM_METHOD_ENABLED
            ]
        )
            ->order('updated_at ' . \Magento\Framework\DB\Select::SQL_DESC)
            ->limit(1);
    }

    /**
     * @param \Zend_Db_Select $select
     */
    private function applyActiveFilter($select)
    {
        $select->where('amquote.status =? ', Status::CREATED);
    }

    /**
     * @inheritdoc
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $this->joinAmastyQuote($select);

        return $select;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return $this
     */
    protected function processNotModifiedSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->saveAmastyQuote($object);
        return parent::processNotModifiedSave($object);
    }
}
