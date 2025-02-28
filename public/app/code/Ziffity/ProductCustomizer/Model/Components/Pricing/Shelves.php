<?php
declare(strict_types=1);

namespace Ziffity\ProductCustomizer\Model\Components\Pricing;

use Ziffity\ProductCustomizer\Helper\Data as Helper;
use Ziffity\ProductCustomizer\Model\Components\Measurements\FrameSize;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Shelves
{
    const SHELVES_1_4_PRICE_CONFIG_PATH = 'custom_frame/component_price/shelves_1_4_price';
    const SHELVES_3_8_PRICE_CONFIG_PATH = 'custom_frame/component_price/shelves_3_8_price';

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
     * @param Helper $helper
     * @param FrameSize $frameSize
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Helper $helper,
        FrameSize $frameSize,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->helper = $helper;
        $this->frameSize = $frameSize;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return float
     */
    public function getConfigOneQuarterPrice()
    {
        return (float)$this->scopeConfig->getValue(
            self::SHELVES_1_4_PRICE_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return float
     */
    public function getConfigThreeEightsPrice()
    {
        return (float)$this->scopeConfig->getValue(
            self::SHELVES_3_8_PRICE_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param  []$selection of current product.
     *
     * @return float
     */
    public function getPrice($selectionData)
    {
        $shelvesDepth = $this->helper->fractionalToFloat($selectionData['size']['thickness']);
        $shelvesWidth = $this->frameSize->getInnerFrameWidth($selectionData);
        $perShelvesPrice = 0;
        $qty = 0;
        $thickness = 0;
        if (isset($selectionData['shelves'])) {
            foreach ($selectionData['shelves'] as $key => $value) {
                if ($key === 'shelves_qty') {
                    if ( 0 == $value) {
                        return 0;
                    } else {
                        $qty = (int)$value;
                    }
                } elseif ($key === 'shelves_thickness') {
                    $thickness = $value;
                }
            }
            if ($thickness == '0.25') {
                $perShelvesPrice = $this->getConfigOneQuarterPrice();
            } elseif ($thickness == '0.375') {
                $perShelvesPrice = $this->getConfigThreeEightsPrice();
            }
            return (float)$qty * $shelvesWidth * $shelvesDepth * $perShelvesPrice;
        }
        return 0;
    }
}
