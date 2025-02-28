<?php

namespace Ziffity\Shipping\Test\Unit\Block\Adminhtml\OversizeProfile\Edit\Button;

use Ziffity\Shipping\Block\Adminhtml\Block\Edit\OversizeProfile\DeleteButton;
use Ziffity\Shipping\Test\Unit\Block\Adminhtml\GenericTest;

class DeleteButtonTest extends GenericTest
{
    /**
     * @covers \Ziffity\Shipping\Block\Adminhtml\Block\Edit\OversizeProfile\DeleteButton::getButtonData
     * @return void
     */
    public function testGetButtonData()
    {
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with('*/oversize_profile/delete', ['profile_id'=>'1'])
            ->willReturn('/');

        $this->contextMock->expects($this->atLeastOnce())
            ->method('getRequestParam')
            ->willReturn('1');

        $actual = [
            'label' => __('Delete'),
            'class' => 'delete',
            'on_click' => 'deleteConfirm(\'' . __(
                'Are you sure you want to do this?'
            ) . '\', \'' . '/' . '\', {"data": {}})',
            'sort_order' => 20,
        ];

        $expected = $this->getModel(DeleteButton::class)->getButtonData();

        $this->assertEquals($actual, $expected);
    }
}
