<?php

namespace Ziffity\Shipping\Model\Carrier;

use Ziffity\Shipping\Helper\Weight;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Ziffity\Shipping\Model\OversizeProfileCharge\ResourceModel\OversizeProfileCharge\CollectionFactory as Collection;

class CalculateOversizeProfilePrice
{

    protected $weightHelper;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @param Collection $collection
     */
    public function __construct(Collection $collection,Weight $weightHelper)
    {
        $this->collection = $collection;
        $this->weightHelper = $weightHelper;
    }

    /**
     * @param array|object $request
     * @param ProductRepositoryInterface $productRepository
     * @return float|int
     */
    public function calculate($request, $productRepository)
    {
        $oversizeAmount = 0;
        foreach ($request as $item) {
            if ($item->getHasChildren() && $item->isShipSeparately()) {
                foreach ($item->getChildren() as $child) {
                    if (!$child->getProduct()->isVirtual()) {
                        $oversizeAmount += $this->getOversizeProfileFee($item, $productRepository);
                    }
                }
            } elseif (!$item->getProduct()->isVirtual()) {
                $oversizeAmount += $this->getOversizeProfileFee($item, $productRepository);
            }
        }
        return $oversizeAmount;
    }

    /**
     * @param array|object $item
     * @param ProductRepositoryInterface $productRepository
     * @return float
     */
    protected function getOversizeProfileFee($item, $productRepository)
    {
        $product = $item->getProduct();
        $qty = $item->getQty();
        $productCollection = $productRepository->getbyId($product->getId(),false);
        $oversizeProfile = $productCollection->getCustomAttribute('oversize_profile');
        if ($oversizeProfile) {
            $profileId = $oversizeProfile->getValue();
            $unitedInches = $this->getItemSize($item);
            $fee = $this->getPrice($unitedInches, $profileId);
            $fee *= round($qty / 2);
            return $fee;
        }
        return 0;
    }

    /**
     * @param int|float $unitedInches
     * @param int|null $profileId
     * @return float
     */
    public function getPrice($unitedInches, $profileId = null)
    {
        if (empty($profileId)) {
            return 0;
        }

        $collection = $this->collection->create();
        $charge = $collection->addFieldToSelect(['shipping_charge', 'shipping_charge_type'])
            ->addFieldToFilter(
                'united_inch_min',
                [
                    'or' => [
                        0 => ['to' => (float) $unitedInches],
                        1 => ['is' => new \Zend_Db_Expr('null')]
                    ]
                ]
            )
            ->addFieldToFilter(
                'united_inch_max',
                [
                    'or' => [
                        0 => ['from' => (float) $unitedInches],
                        1 => ['is' => new \Zend_Db_Expr('null')]]
                ]
            )
            ->addFieldToFilter(
                ['united_inch_max', 'united_inch_min'],
                [
                    ['is' => new \Zend_Db_Expr('not null')],
                    ['is' => new \Zend_Db_Expr('not null')]
                ]
            )
            ->addFieldToFilter('profile_id', ['eq' => $profileId])
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem();

        $oversizeCharge = $charge->getShippingCharge();

        if (empty($oversizeCharge)) {
            $oversizeCharge = 0;
        }

        $shippingChargeType = $charge->getShippingChargeType();

        if ($shippingChargeType == 2) {
            $oversizeCharge = ($unitedInches / 100) * $oversizeCharge;
        }

        return (float) $oversizeCharge;
    }

    /**
     * @param array|object $item
     * @return int
     */
    protected function getItemSize($item)
    {
        $product = $item->getProduct();
        $this->weightHelper->setProduct($product);
        $this->weightHelper->calculateQuoteItemWeight($item);
        $height = $this->weightHelper->getOverallFrameHeight();
        $width = $this->weightHelper->getOverallFrameWidth();
        return $height + $width;
    }
}
