<?php
declare(strict_types=1);

namespace Ziffity\ProductCustomizer\Model\Components\Pricing;

use Magento\Catalog\Api\Data\ProductInterface;

class Corkboards extends \Ziffity\ProductCustomizer\Model\Components\Pricing\AbstractPrice
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
        $pricePerInch = $product->getData('price');
        $width = $this->frameSize->getOverallWidth($selectionData);
        $height = $this->frameSize->getOverallHeight($selectionData);
        $backOfMouldingWidth = $this->frameSize->getBackOfMouldingWidth($selectionData);
        $width = $width - ($backOfMouldingWidth * 2) - 0.0625;
        $height = $height - ($backOfMouldingWidth * 2) - 0.0625;

        $width = round((float) $width, 4);
        $height = round((float) $height, 4);

        $area = ((float) $width * (float) $height);
        $area = round((float) $area, 4);

        $initialPrice = $area * $pricePerInch;
        return $this->getCalculatedFinalPrice($product, $initialPrice);
    }
}
