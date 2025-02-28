<?php

namespace Ziffity\Shipping\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Ziffity\CustomFrame\Model\Product\Type;
use Ziffity\Shipping\Helper\Weight as WeightHelper;
use Ziffity\Shipping\Model\Carrier\CalculateOversizeProfilePrice;

class OversizeCalculation
{

    /**
     * @var CalculateOversizeProfilePrice
     */
    protected $oversizeProfile;

    /**
     * @var WeightHelper
     */
    protected $weightHelper;

    /**
     * @var ProductRepositoryInterface
     */
    public $productRepository;

    /**
     * @param WeightHelper $weightHelper
     * @param ProductRepositoryInterface $productRepository
     * @param CalculateOversizeProfilePrice $oversizeProfile
     */
    public function __construct(
        WeightHelper $weightHelper,
        ProductRepositoryInterface $productRepository,
        CalculateOversizeProfilePrice $oversizeProfile
    )
    {
        $this->weightHelper = $weightHelper;
        $this->productRepository = $productRepository;
        $this->oversizeProfile = $oversizeProfile;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return float|int
     */
    public function calculate($quote)
    {
        $oversizeAmount = 0;
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getHasChildren() && $item->isShipSeparately()) {
                foreach ($item->getChildren() as $child) {
                    if (!$child->getProduct()->isVirtual()) {
                        $oversizeAmount += $this->getOversizeProfileFee($item);
                    }
                }
            } else if (!$item->getProduct()->isVirtual()) {
                $oversizeAmount += $this->getOversizeProfileFee($item);
            }
        }
        return $oversizeAmount;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return float|mixed
     * @throws NoSuchEntityException
     */
    protected function getOversizeProfileFee($item)
    {
        $profileId = null;
        $product = $item->getProduct();
        $qty = $item->getQty();
        $productModel = $this->productRepository->getById($product->getId(),false);
        $attribute = $productModel->getCustomAttribute('oversize_profile');
        if ($attribute) {
            $profileId = $attribute->getValue();
        }
        $unitedInches    = $this->getItemSize($item);
        $fee             = $this->oversizeProfile->getPrice($unitedInches, $profileId);
        $fee             *= round($qty / 2);
        return $fee;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param \Ziffity\Shipping\Model\Carrier\Shipping $item
     * @return float|int
     */
    protected function getItemSize($item)
    {
        $product = $item->getProduct();
        $productTypeId = $product->getTypeId();
        if ($productTypeId == Type::TYPE_CODE) {
            $this->weightHelper->setProduct($product);
            $this->weightHelper->calculateQuoteItemWeight($item);
            $height = $this->weightHelper->getOverallFrameHeight();
            $width = $this->weightHelper->getOverallFrameWidth();
            return $height + $width;
        }
        return 0;
    }
}
