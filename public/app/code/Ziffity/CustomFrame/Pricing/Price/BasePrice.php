<?php

namespace Ziffity\CustomFrame\Pricing\Price;

use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;

/**
 * Class BasePrice
 */
class BasePrice extends AbstractPrice
{
    /**
     * Price type identifier string
     */
    const PRICE_CODE = 'base_price';

    /**
     * Get Base Price Value
     *
     * @return float|bool
     */
    public function getValue()
    {
        if ($this->value === null) {
            $this->value = false;
            foreach ($this->priceInfo->getPrices() as $price) {
                if ($price instanceof BasePriceProviderInterface && $price->getValue() !== false) {
                    $this->value = min($price->getValue(), $this->value !== false ? $this->value: $price->getValue());
                }
            }
        }
        if($this->getProduct()){
            $this->value = $this->getProduct()->getMinPrice();
        }
        return $this->value;
    }
}
