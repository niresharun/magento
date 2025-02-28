<?php
declare(strict_types=1);

namespace Ziffity\CustomFrame\Pricing\Adjustment;

use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Pricing\Price\BundleSelectionFactory;
use Magento\Bundle\Pricing\Price\BundleSelectionPrice;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Adjustment\Calculator as CalculatorBase;
use Magento\Framework\Pricing\Amount\AmountFactory;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Store;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Bundle\Pricing\Adjustment\Calculator as BundleCalculator;
use Magento\Bundle\Pricing\Adjustment\SelectionPriceListProviderInterface;
use Ziffity\ProductCustomizer\Model\CompositeConfigProvider;
use Ziffity\CustomFrame\Model\Product\Price as FramePrice;

/**
 * CustomFrame price calculator
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Calculator extends BundleCalculator
{
    /**
     * @var CalculatorBase
     */
    protected $calculator;

    /**
     * @var AmountFactory
     */
    protected $amountFactory;

    /**
     * @var BundleSelectionFactory
     */
    protected $selectionFactory;

    /**
     * Tax helper, needed to get rounding setting
     *
     * @var TaxHelper
     */
    protected $taxHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var AmountInterface[]
     */
    private $optionAmount = [];

    /**
     * @var SelectionPriceListProviderInterface
     */
    private $selectionPriceListProvider;

    protected $compositeConfigProvider;

    protected $framePrice;

    /**
     * @param CalculatorBase $calculator
     * @param AmountFactory $amountFactory
     * @param BundleSelectionFactory $bundleSelectionFactory
     * @param TaxHelper $taxHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param SelectionPriceListProviderInterface $selectionPriceListProvider
     */
    public function __construct(
        CalculatorBase $calculator,
        AmountFactory $amountFactory,
        BundleSelectionFactory $bundleSelectionFactory,
        TaxHelper $taxHelper,
        PriceCurrencyInterface $priceCurrency,
        SelectionPriceListProviderInterface $selectionPriceListProvider,
        CompositeConfigProvider $compositeConfigProvider,
        FramePrice $framePrice
    ) {
        $this->calculator = $calculator;
        $this->amountFactory = $amountFactory;
        $this->selectionFactory = $bundleSelectionFactory;
        $this->taxHelper = $taxHelper;
        $this->priceCurrency = $priceCurrency;
        $this->selectionPriceListProvider = $selectionPriceListProvider;
        $this->compositeConfigProvider = $compositeConfigProvider;
        $this->framePrice = $framePrice;
    }

    /**
     * Option amount calculation for bundle product
     *
     * @param Product $saleableItem
     * @param null|bool|string|array $exclude
     * @param bool $searchMin
     * @param float $baseAmount
     * @param bool $useRegularPrice
     *
     * @return AmountInterface
     */
    public function getOptionsAmount(
        Product $saleableItem,
                $exclude = null,
                $searchMin = true,
                $baseAmount = 0.,
                $useRegularPrice = false
    ) {
        if($saleableItem->getTypeId() == 'customframe'){
            $useRegularPrice = true;
        }
        $cacheKey = implode('-', [$saleableItem->getId(), $exclude, $searchMin, $baseAmount, $useRegularPrice]);
        if (!isset($this->optionAmount[$cacheKey])) {
            $this->optionAmount[$cacheKey] = $this->calculateBundleAmount(
                $baseAmount,
                $saleableItem,
                $this->getSelectionAmounts($saleableItem, $searchMin, $useRegularPrice),
                $exclude
            );
        }

        return $this->optionAmount[$cacheKey];
    }


    /**
     * Calculate amount for dynamic bundle product
     *
     * @param float $basePriceValue
     * @param Product $bundleProduct
     * @param BundleSelectionPrice[] $selectionPriceList
     * @param null|bool|string|array $exclude
     * @return AmountInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function calculateDynamicBundleAmount($basePriceValue, $bundleProduct, $selectionPriceList, $exclude)
    {
        $fullAmount = 0.;
        $adjustments = [];
        $i = 0;

        if($bundleProduct->getTypeId() == 'customframe'){
            $defaultOptions = $this->compositeConfigProvider->getDefaultConfig($bundleProduct);
            $fullAmount =  $this->framePrice->getPrice($bundleProduct, $defaultOptions['options']);
            return $this->amountFactory->create($fullAmount, $adjustments);
        }
        $amountList[$i]['amount'] = $this->calculator->getAmount($basePriceValue, $bundleProduct, $exclude);
        $amountList[$i]['quantity'] = 1;

        foreach ($selectionPriceList as $selectionPrice) {
            ++$i;
            if ($selectionPrice) {
                $amountList[$i]['amount'] = $selectionPrice->getAmount();
                // always honor the quantity given
                $amountList[$i]['quantity'] = $selectionPrice->getQuantity();
            }
        }

        /** @var  Store $store */
        $store = $bundleProduct->getStore();
        $roundingMethod = $this->taxHelper->getCalculationAlgorithm($store);
        foreach ($amountList as $amountInfo) {
            /** @var AmountInterface $itemAmount */
            $itemAmount = $amountInfo['amount'];
            $qty = $amountInfo['quantity'];

            if ($roundingMethod != TaxCalculationInterface::CALC_TOTAL_BASE) {
                //We need to round the individual selection first
                $fullAmount += ($this->priceCurrency->round($itemAmount->getValue()) * $qty);
                foreach ($itemAmount->getAdjustmentAmounts() as $code => $adjustment) {
                    $adjustment = $this->priceCurrency->round($adjustment) * $qty;
                    $adjustments[$code] = isset($adjustments[$code]) ? $adjustments[$code] + $adjustment : $adjustment;
                }
            } else {
                $fullAmount += ($itemAmount->getValue() * $qty);
                foreach ($itemAmount->getAdjustmentAmounts() as $code => $adjustment) {
                    $adjustment = $adjustment * $qty;
                    $adjustments[$code] = isset($adjustments[$code]) ? $adjustments[$code] + $adjustment : $adjustment;
                }
            }
        }
        if (is_array($exclude) == false) {
            if ($exclude && isset($adjustments[$exclude])) {
                $fullAmount -= $adjustments[$exclude];
                unset($adjustments[$exclude]);
            }
        } else {
            foreach ($exclude as $oneExclusion) {
                if ($oneExclusion && isset($adjustments[$oneExclusion])) {
                    $fullAmount -= $adjustments[$oneExclusion];
                    unset($adjustments[$oneExclusion]);
                }
            }
        }
        return $this->amountFactory->create($fullAmount, $adjustments);
    }
}
