<?php
declare(strict_types=1);

namespace Ziffity\ProductCustomizer\Model\Components\Pricing;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\Registry;
use Ziffity\Coproduct\Model\ProcessRule;
use Ziffity\ProductCustomizer\Helper\Data as Helper;
use Ziffity\ProductCustomizer\Model\Components\Measurements\FrameSize;

class Backingboard extends \Ziffity\ProductCustomizer\Model\Components\Pricing\AbstractPrice
{

    /**
     * @param Helper $helper
     * @param PriceCurrency $priceCurrency
     * @param FrameSize $frameSize
     * @param Registry $registry
     * @param ProcessRule $processRule
     */
    public function __construct(
        Helper $helper,
        PriceCurrency $priceCurrency,
        FrameSize $frameSize,
        Registry $registry,
        private ProcessRule $processRule
    ) {
        parent::__construct($helper, $priceCurrency, $frameSize, $registry);
    }

    /**
     * @param  ProductInterface $product product object.
     * @param  []$selectionData of current product.
     *
     * @return float
     */
    public function getPrice($product, $selectionData = null)
    {
        $initialPrice = $this->processRule->applyPrimary($product, $selectionData, 'backingboard_type');
        $initialPrice = ($initialPrice <= 0) ? $product->getPrice() : $initialPrice;
        return $this->getCalculatedFinalPrice($product, $initialPrice);
    }
}
