<?php declare(strict_types=1);

namespace Ziffity\ProductCustomizer\Block\Product\LayoutProcessor;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

class ProductCustomizer implements LayoutProcessorInterface
{
    public function process($jsLayout): array
    {

        return $jsLayout;
    }
}