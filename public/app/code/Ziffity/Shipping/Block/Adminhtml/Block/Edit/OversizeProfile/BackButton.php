<?php

namespace Ziffity\Shipping\Block\Adminhtml\Block\Edit\OversizeProfile;

use Ziffity\Shipping\Block\Adminhtml\Block\Edit\GenericButton;

class BackButton extends GenericButton
{
    /**
     * This function returns the button data with url.
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    private function getBackUrl()
    {
        return $this->getUrl('shipping/oversize_profile/grid');
    }
}
