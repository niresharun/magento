<?php

namespace Ziffity\DigitalSigning\Test\Unit\Block\Adminhtml;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Backend\Block\Widget\Context;
use Ziffity\DigitalSigning\Block\Adminhtml\DigitalSign;
use Ziffity\DigitalSigning\Model\ImageDataFactory;
use Ziffity\DigitalSigning\Model\ImageData;

class DigitalSignTest extends TestCase
{

    /**
     * @var (Context&MockObject)|MockObject
     */
    private $contextMock;

    /**
     * @var DigitalSign
     */
    public $controller;
    
    /**
     * @var Http
     */
    protected $requestInterface;

    /**
     * @var ImageDataFactory
     */
    protected $imageDataFactory;

    public function setUp(): void
    {

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->requestInterface = $this->getMockBuilder(Http::class)
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->imageDataFactory = $this->getMockBuilder(ImageDataFactory::class)
        ->setMethods(['create'])
        ->disableOriginalConstructor()
        ->getMock();

        $this->imageData = $this->createMock(ImageData::class);

        $this->imageDataFactory->method('create')->willReturn($this->imageData);
        $this->controller = (new ObjectManagerHelper($this))->getObject(
            DigitalSign::class,
            [
                'context' => $this->contextMock,
                'request' => $this->requestInterface,
                'modelDataFactory' => $this->imageDataFactory,
            ]
        );
    }

    /**
     *
     * @covers \Ziffity\DigitalSigning\Block\Adminhtml\DigitalSign::getOrderId
     * @return void
     * @throws LocalizedException
     * @throws NotFoundException
     */
    public function testGetOrderId()
    {
        $orderId = '101';
        $expected = '90';
        $this->requestInterface->expects($this->any())->method('getParam')->willReturn($orderId);
        $this->imageData->expects($this->any())
            ->method('load')
            ->willReturn($expected);
        $actual = $this->controller->getOrderId();
        $this->assertEquals($expected, $actual);
    }
}
