<?php
declare(strict_types=1);

namespace Ziffity\ProductCustomizer\Model\Components\Pricing;

use Magento\Directory\Model\PriceCurrency;
use Ziffity\ProductCustomizer\Helper\Data as Helper;
use Ziffity\ProductCustomizer\Model\Components\Measurements\FrameSize;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Lighting
{
    const POWER_SUPPLY_PRICE_CONFIG_PATH = 'custom_frame/component_price/power_supply';
    const POWER_CONNECTION_PRICE_CONFIG_PATH = 'custom_frame/component_price/power_connection';
    const POWER_CONNECTION_HARDWIRED_PRICE_CONFIG_PATH = 'custom_frame/component_price/power_connection_hardwired';
    const POWER_CONNECTION_PLUG_PRICE_CONFIG_PATH = 'custom_frame/component_price/power_connection_plug';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var FrameSize
     */
    protected $frameSize;

    /**
     * @var PriceCurrency
     */
    protected $priceCurrency;

    /**
     * @param Helper $helper
     * @param FrameSize $frameSize
     * @param ScopeConfigInterface $scopeConfig
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(
        Helper $helper,
        FrameSize $frameSize,
        ScopeConfigInterface $scopeConfig,
        PriceCurrency $priceCurrency
    ) {
        $this->helper = $helper;
        $this->frameSize = $frameSize;
        $this->scopeConfig = $scopeConfig;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @return float
     */
    public function getConfigPowerSupplyPrice()
    {
        return $this->getConfig(self::POWER_SUPPLY_PRICE_CONFIG_PATH);
    }

    /**
     * @return float
     */
    public function getConfigPowerConnectionPrice()
    {
        return $this->getConfig(self::POWER_CONNECTION_PRICE_CONFIG_PATH);
    }

    /**
     * @return float
     */
    public function getConfigPowerConnectionHardwiredPrice()
    {
        return $this->getConfig(self::POWER_CONNECTION_HARDWIRED_PRICE_CONFIG_PATH);
    }

    /**
     * @return float
     */
    public function getConfigPowerConnectionPlugPrice()
    {
        return $this->getConfig(self::POWER_CONNECTION_PLUG_PRICE_CONFIG_PATH);
    }

    /**
     * @return float
     */
    public function getConfig($path)
    {
        return (float)$this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param  []$selectionData of current product.
     * @return float
     */
    public function getLightingTopPrice($selectionData)
    {
        $innerFrameWidth = $this->frameSize->getInnerFrameWidth($selectionData);
        $powerSupply = $this->getConfigPowerSupplyPrice();
        $powerConnection = $this->getConfigPowerConnectionPrice();

        // Converted calculation from M1
        return ($innerFrameWidth * 1.21) + $powerConnection + $powerSupply;
    }

    /**
     * @param  []$selectionData of current product.
     * @return float
     */
    public function getLightingPerimeterPrice($selectionData)
    {
        $innerFrameWidth = $this->frameSize->getInnerFrameWidth($selectionData);
        $innerFrameHeight = $this->frameSize->getInnerFrameHeight($selectionData);
        $powerSupply = $this->getConfigPowerSupplyPrice();
        $powerConnection = $this->getConfigPowerConnectionPrice();

        // Converted calculation from M1
        return (($innerFrameWidth * 2) + ($innerFrameHeight * 2)) * 1.21 + $powerConnection + $powerSupply;
    }

    /**
     * @param  []$selectionData of current product.
     *
     * @return float
     */
    public function getPrice($selectionData)
    {
        $price = 0;
        if (isset($selectionData['lighting']['form_data'])) {
            foreach ($selectionData['lighting']['form_data'] as $key => $value) {
                if ('power_connection' === $key) {
                    $methodName = "getConfigPowerConnection".ucfirst($value)."Price";
                    if (method_exists($this, $methodName)) {
                        $price += $this->$methodName();
                    }
                } elseif ('lighting_position' === $key) {
                    $methodName = "getLighting".ucfirst($value)."Price";
                    if (method_exists($this, $methodName)) {
                        $price += $this->$methodName($selectionData);
                    }
                }
            }
        }
        return $this->priceCurrency->roundPrice($price);
    }
}
