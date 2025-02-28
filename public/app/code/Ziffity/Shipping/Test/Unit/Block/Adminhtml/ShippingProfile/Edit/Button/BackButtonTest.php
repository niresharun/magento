<?php

namespace Ziffity\Shipping\Test\Unit\Block\Adminhtml\ShippingProfile\Edit\Button;

use Ziffity\Shipping\Block\Adminhtml\Block\Edit\ShippingProfile\BackButton;
use Ziffity\Shipping\Test\Unit\Block\Adminhtml\GenericTest;

class BackButtonTest extends GenericTest
{
    /**
     * @covers \Ziffity\Shipping\Block\Adminhtml\Block\Edit\ShippingProfile\BackButton::getButtonData
     * @return void
     */
    public function testGetButtonData()
    {
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with('shipping/shipping_profile/grid', [])
            ->willReturn('/');

        $this->assertEquals(
            [
                'label' => __('Back'),
                'on_click' => sprintf("location.href = '%s';", '/'),
                'class' => 'back',
                'sort_order' => 10
            ],
            $this->getModel(BackButton::class)->getButtonData()
        );
    }
}
