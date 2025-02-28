<?php
declare(strict_types=1);

namespace Ziffity\ProductCustomizer\Model\Components\Pricing;

use Magento\Catalog\Api\Data\ProductInterface;

class Postfinish extends \Ziffity\ProductCustomizer\Model\Components\Pricing\AbstractPrice
{
    /**
     * Get overall frame width.
     *
     * @param  ProductInterface $product product object.
     * @param  []$selectionData of current product.
     *
     * @return float
     */
    public function getPrice($product, $selectionData = null)
    {
        $initialPrice = $product->getData('price');
        return $this->getCalculatedFinalPrice($product, $initialPrice);
    }
}
