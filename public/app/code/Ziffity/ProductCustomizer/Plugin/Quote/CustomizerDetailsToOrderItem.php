<?php

namespace Ziffity\ProductCustomizer\Plugin\Quote;

use Magento\Catalog\Block\Product\ProductList\Toolbar;

class CustomizerDetailsToOrderItem
{
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {
        /** @var $orderItem Item */
        $orderItem = $proceed($item, $additional);
        $orderItem->setCustomizerDetails($item->getCustomizerDetails());
        return $orderItem;
    }
}
