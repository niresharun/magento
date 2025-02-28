<?php

namespace Ziffity\CustomFrame\Plugin;

use Magento\Catalog\Model\Product\Attribute\Backend\Price;
use Ziffity\CustomFrame\Model\Product\Type;
use Magento\Catalog\Model\Product;

class PriceBackend
{
    /**
     * This function over-rides the validation functionality of pricing used in bundle.
     *
     * @param Price $subject
     * @param callable $proceed
     * @param Product $object
     * @return bool
     */
    public function aroundValidate(Price $subject, callable $proceed, $object): bool
    {
        if ($object instanceof \Magento\Catalog\Model\Product
            && $object->getTypeId() == Type::TYPE_CODE
            && $object->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC
        ) {
            return true;
        }
        return $proceed($object);
    }
}
