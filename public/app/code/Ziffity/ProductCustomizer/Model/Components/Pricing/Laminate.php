<?php
declare(strict_types=1);

namespace Ziffity\ProductCustomizer\Model\Components\Pricing;

use Magento\Catalog\Api\Data\ProductInterface;

class Laminate extends \Ziffity\ProductCustomizer\Model\Components\Pricing\AbstractPrice
{
    /**
     * Get overall frame width.
     *
     * @param  ProductInterface $product product object.
     * @param  []$selectionData of current product.
     *
     * @return float
     */
    public function getPrice($product, $code,  $selectionData = null)
    {
        $initialPrice = $this->calculatePrice($product, $selectionData, $code);
        return $this->getCalculatedFinalPrice($product, $initialPrice);
    }

    /**
     * Calculate price
     *
     * @param ProductInterface $product
     * @return string
     */
    protected function calculatePrice($product, $selectionData, $code)
    {
        if ($code === 'Laminate Exterior') {
            return $this->calculateExterior($product, $selectionData);
        }
        if ($code === 'Laminate Interior') {
            return $this->calculateInterior($product, $selectionData);
        }
        return $product->getData('price');
    }

    /**
     * Calculate interior price
     *
     * @param ProductInterface $product
     * @param []$selectionData
     *
     * @return float
     */
    protected function calculateInterior($product, $selectionData)
    {
        $width = $this->frameSize->getInnerFrameWidth($selectionData);
        $height = $this->frameSize->getInnerFrameHeight($selectionData);
        $depth = 0;
        if (!empty($selectionData['size']['thickness'])) {
            $depth = $this->helper->fractionalToFloat($selectionData['size']['thickness']);/* TODO: $wallThickness for box_thickness */
        /* TODO: fetchThicknessList  */
        } elseif ($list = []) { // $this->helper->fetchThicknessList($product) out of scope
            $depth = reset($list);
        }

        return ((($width * $depth) * 2) + (($height * $depth) * 2) + ($width * $height)) * $product->getData('price');
    }

    /**
     * Calculate exterior price
     *
     * @param ProductInterface $product
     * @param []$selectionData
     *
     * @return float
     */
    protected function calculateExterior($product, $selectionData)
    {
        /* TODO: $wallThickness for box_thickness */
        //  $wallThickness = $template->getData('box_thickness') ? $template->getData('box_thickness') : 0;
        $wallThickness = 0;
        $width = $this->frameSize->getInnerFrameWidth($selectionData);
        $height = $this->frameSize->getInnerFrameHeight($selectionData);
        $depth = 0;
        if (!empty($selectionData['size']['thickness'])) {
            $depth = $this->helper->fractionalToFloat($selectionData['size']['thickness']);
        /* TODO: fetchThicknessList */
        } elseif ($list = []) { // $this->helper->fetchThicknessList($product) out of scope
            $depth = reset($list);
        }

        return ( (($width + 2 * $wallThickness) * $wallThickness * 2) +
                (($height + 2 * $wallThickness) * $wallThickness * 2) +
                (($width + 2 * $wallThickness) * ($depth + 1) * 2) +
                (($height + 2 * $wallThickness) * ($depth + 1) * 2) ) * $product->getData('price');
    }
}
