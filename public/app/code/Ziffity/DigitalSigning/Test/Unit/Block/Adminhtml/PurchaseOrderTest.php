<?php

namespace Ziffity\DigitalSigning\Test\Unit\Block\Adminhtml;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Backend\Block\Widget\Context;
use Ziffity\DigitalSigning\Block\Adminhtml\PurchaseOrder;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection;

class PurchaseOrderTest extends TestCase
{

    /**
     * @var (Context&MockObject)|MockObject
     */
    private $contextMock;

    /**
     * @var PurchaseOrder
     */
    public $controller;

    /**
     * @var Http
     */
    protected $requestInterface;

    /**
     * @var CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $salesOrderCollectionFactoryMock;

    public function setUp(): void
    {

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->requestInterface = $this->getMockBuilder(Http::class)
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->salesOrderCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
        ->setMethods(['create'])
        ->disableOriginalConstructor()
        ->getMock();

        $this->salesOrderCollectionMock = $this->createMock(Collection::class);

        $this->salesOrderCollectionFactoryMock->method('create')->willReturn($this->salesOrderCollectionMock);
        $this->controller = (new ObjectManagerHelper($this))->getObject(
            PurchaseOrder::class,
            [
                'context' => $this->contextMock,
                'request' => $this->requestInterface,
                'collectionFactory' => $this->salesOrderCollectionFactoryMock,
            ]
        );
    }

    /**
     *
     * @covers \Ziffity\DigitalSigning\Block\Adminhtml\PurchaseOrder::getPurchaseOrder
     * @return void
     * @throws LocalizedException
     * @throws NotFoundException
     */
    public function testGetPurchaseOrder()
    {
        $orderId = '101';
        $expected = 'hgchgshdhjs236873676e868721';
        $this->requestInterface->expects($this->any())->method('getParam')->willReturn($orderId);

        $this->salesOrderCollectionMock->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturn($expected);
        
        $actual = $this->controller->getPurchaseOrder();
            $this->assertEquals($expected, $actual);
    }
}
