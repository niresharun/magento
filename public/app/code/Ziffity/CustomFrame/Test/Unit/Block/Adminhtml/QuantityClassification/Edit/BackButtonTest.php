<?php

declare(strict_types=1);

namespace Ziffity\CustomFrame\Test\Unit\Block\Adminhtml\QuantityClassification\Edit;

use Ziffity\CustomFrame\Block\Adminhtml\QuantityClassification\Edit\BackButton;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class BackButtonTest extends TestCase
{
    /**
     * @var BackButton
     */
    protected $block;

    /**
     * @var MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Context
     */
    protected $context;
    
    protected function setUp(): void
    {
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['registry'])
            ->getMock();
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();
            
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->block = $objectManagerHelper->getObject(
            BackButton::class,
            [
                'context' => $this->context,
                'registry' => $this->registry,
                'urlBuilder' => $this->urlBuilderMock
            ]
        );
    }

    public function testGetButtonData()
    {
        $backUrl = '*/index/';
        $expectedResult = [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $backUrl),
            'class' => 'back',
            'sort_order' => 10
        ];

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/index/')
            ->willReturn($backUrl);

        $this->assertEquals($expectedResult, $this->block->getButtonData());
    }
}
