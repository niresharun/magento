<?php
declare(strict_types=1);
namespace Ziffity\ProductCustomizer\Model\Components\Pricing;

use Magento\Framework\Registry;
use Ziffity\ProductCustomizer\Helper\Data as Helper;
use Ziffity\ProductCustomizer\Model\Components\Measurements\FrameSize;
use Magento\Directory\Model\PriceCurrency;
use Magento\Catalog\Api\Data\ProductInterface;

abstract class AbstractPrice
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var PriceCurrency
     */
    protected $priceCurrency;

    /**
     * @var FrameSize
     */
    protected $frameSize;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Helper $helper
     * @param PriceCurrency $priceCurrency
     * @param FrameSize $frameSize
     * @param Registry $registry
     */
    public function __construct(
        Helper $helper,
        PriceCurrency $priceCurrency,
        FrameSize $frameSize,
        Registry $registry
    ) {
        $this->helper = $helper;
        $this->priceCurrency = $priceCurrency;
        $this->frameSize = $frameSize;
        $this->registry = $registry;
    }

    /**
     * Get calculated final price.
     *
     * @param  ProductInterface $product product object.
     * @param  float $initialPrice
     *
     * @return float
     */
    protected function getCalculatedFinalPrice($product, $initialPrice)
    {
        $calculatedPrice = $initialPrice;

        /** @var float $laborFactor */
        $laborFactor = $product->getLaborFactor();
        /** @var float $wasteFactor */
        $wasteFactor = $product->getWasteFactor();

        $laborAndWaste = 0;
        if (!empty($laborFactor)) {
            $laborAndWaste += $this->helper->calculatePricePercentage($calculatedPrice, $laborFactor);
        }
        if (!empty($wasteFactor)) {
            $laborAndWaste += $this->helper->calculatePricePercentage($calculatedPrice, $wasteFactor);
        }
        $calculatedPrice += $laborAndWaste;

        $more_factors = array(
            'freight_in_factor',
            'overhead_factor',
            'packaging_factor',
        );
        foreach($more_factors as $factor){
            $factorValue = floatval($product->getData($factor));
            if($factorValue > 0){
                $factorPercentage = $this->helper->calculatePricePercentage($initialPrice, $factorValue);
                $calculatedPrice += $factorPercentage;
            }
        }

        $profitPercentage = floatval($product->getData("profit_percentage"));
        if($profitPercentage > 0){
            $calculatedPrice *= ($profitPercentage / 100);
        }
        return $this->priceCurrency->roundPrice($calculatedPrice);
    }
}

