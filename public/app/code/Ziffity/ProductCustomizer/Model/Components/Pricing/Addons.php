<?php
declare(strict_types=1);

namespace Ziffity\ProductCustomizer\Model\Components\Pricing;

use Magento\Catalog\Api\Data\ProductInterface;

class Addons
{
    const ADDON_PLUNGE_CONFIG_PATH = 'custom_frame/component_price/addon_plunge_price';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @param  \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return float
     */
    public function getConfigPlungePrice()
    {
        return $this->scopeConfig->getValue(
            self::ADDON_PLUNGE_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param  []$selection of current product.
     *
     * @return float
     */
    public function getPrice($selection)
    {
        if (isset($selection['form_data'])) {
            foreach ($selection['form_data'] as $key => $value) {
                    if ($key !== 'plunge_lock') {
                    continue;
                }
                if ($value === 'include') {
                    return (float)$this->getConfigPlungePrice();
                }
            }
        }
        return 0;
    }
}
