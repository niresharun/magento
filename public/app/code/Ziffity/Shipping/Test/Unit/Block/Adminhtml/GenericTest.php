<?php

namespace Ziffity\Shipping\Test\Unit\Block\Adminhtml;

use Ziffity\Shipping\Block\Adminhtml\Block\Edit\GenericButton as Generic;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\Context;
use PHPUnit\Framework\MockObject\MockObject;

class GenericTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param string $class
     * @return object
     */
    protected function getModel($class = Generic::class)
    {
        return $this->objectManager->getObject($class, [
            'context' => $this->contextMock
        ]);
    }

    /**
     * @covers \Ziffity\Shipping\Block\Adminhtml\Block\Edit\GenericButton::getUrl
     * @return void
     */
    public function testGetUrl()
    {
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->willReturn('test_url');

        $this->assertSame('test_url', $this->getModel()->getUrl());
    }
}
