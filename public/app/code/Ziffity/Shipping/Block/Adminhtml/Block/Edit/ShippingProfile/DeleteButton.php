<?php

namespace Ziffity\Shipping\Block\Adminhtml\Block\Edit\ShippingProfile;

use Ziffity\Shipping\Block\Adminhtml\Block\Edit\GenericButton;

class DeleteButton extends GenericButton
{
    /**
     * @inheritDoc
     */
    public function getButtonData()
    {
        $data = [];
        $profileId = $this->context->getRequestParam('profile_id');
        if ($profileId) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getDeleteUrl() . '\', {"data": {}})',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * URL to send delete requests to.
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        $profileId = $this->context->getRequestParam('profile_id');
        return $this->getUrl(
            '*/shipping_profile/delete',
            ['profile_id' => $profileId]
        );
    }
}
