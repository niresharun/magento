<?php

namespace Ziffity\ProductCustomizer\Plugin;

use Magento\Catalog\Block\Product\ProductList\Toolbar;

class DisableModes
{
    /**
     * This plugin disables the grid or list mode in the PLP.
     *
     * @param Toolbar $subject
     * @return bool
     */
    public function afterIsExpanded(Toolbar $subject,$result): bool
    {
        $subject->disableViewSwitcher();
        return $result;
    }
}
