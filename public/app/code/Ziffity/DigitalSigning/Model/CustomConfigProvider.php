<?php

namespace Ziffity\DigitalSigning\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class CustomConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;


    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfig()
    {
        return [
            'digitalSigningThreshold' => $this->scopeConfig->getValue(
                'checkout/digital_signing/order_value_threshold',
                ScopeInterface::SCOPE_STORE
            )
        ];
    }
}
