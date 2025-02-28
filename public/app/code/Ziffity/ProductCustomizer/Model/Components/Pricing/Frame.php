<?php
declare(strict_types=1);

namespace Ziffity\ProductCustomizer\Model\Components\Pricing;

use Magento\Catalog\Api\Data\ProductInterface;

class Frame extends \Ziffity\ProductCustomizer\Model\Components\Pricing\AbstractPrice
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
        $width = !empty($width) ? $width : $product->getData('layer_width');
        $height = !empty($height) ? $height : $product->getData('layer_height');

        // calculated perimeter of selected frame
        $perimeter = (((float)$width + (float)$height) * 2);

        $initialPrice = $perimeter * $pricePerInch;

        return $this->getCalculatedFinalPrice($product, $initialPrice);
    }
}
