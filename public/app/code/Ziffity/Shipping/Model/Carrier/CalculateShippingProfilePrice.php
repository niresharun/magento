<?php

namespace Ziffity\Shipping\Model\Carrier;

use Ziffity\Shipping\Model\ProfileCharge\ResourceModel\ProfileCharge\CollectionFactory as Collection;

class CalculateShippingProfilePrice
{

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @param Collection $collection
     */
    public function __construct(Collection $collection){
        $this->collection = $collection;
    }

    /**
     * @param $itemPrice
     * @param $profileId
     * @return float
     */
    public function getPrice($itemPrice, $profileId = null)
    {
        if (empty($profileId)) {
            //If there is no Profile ID then 0 price will be returned as shipping charge
            return 0;
        }
        /** @var $collection Collection */
        $collection = $this->collection->create();
        $charge = $collection->addFieldToSelect(['shipping_charge', 'shipping_charge_type'])
            ->addFieldToFilter(
                'product_cost_min',
                [
                    'or' => [
                        0 => ['to' => (float) $itemPrice],
                        1 => ['is' => new \Zend_Db_Expr('null')]
                    ]
                ]
            )
            ->addFieldToFilter(
                'product_cost_max',
                [
                    'or' => [
                        0 => ['from' => (float) $itemPrice],
                        1 => ['is' => new \Zend_Db_Expr('null')]]
                ]
            )
            ->addFieldToFilter(
                ['product_cost_max', 'product_cost_min'],
                [
                    ['is' => new \Zend_Db_Expr('not null')],
                    ['is' => new \Zend_Db_Expr('not null')]
                ]
            )
            ->addFieldToFilter('profile_id', ['eq' => $profileId])
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem();
        $shippingCharge = $charge->getShippingCharge();
        if (empty($shippingCharge)) {
            $shippingCharge = 0;
        }
        $shippingChargeType = $charge->getShippingChargeType();
        if ($shippingChargeType == 2) {
            $shippingCharge = ($itemPrice / 100) * $shippingCharge;
        }
        return (float) $shippingCharge;
    }
}
