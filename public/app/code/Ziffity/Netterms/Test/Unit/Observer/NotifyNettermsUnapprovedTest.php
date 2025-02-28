<?php
declare(strict_types=1);

namespace Ziffity\Netterms\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ziffity\Netterms\Observer\NotifyNettermsUnapproved;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Psr\Log\LoggerInterface;
use Ziffity\Netterms\Model\Netterms;
use Ziffity\Netterms\Helper\Data;

class NotifyNettermsUnapprovedTest extends TestCase
{
    /**
     * @var NotifyNettermsUnapproved
     */
    protected $observer;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var Data|MockObject
     */
    private $helperDataMock;

    /**
     * @var Event|MockObject
     */
    protected $event;

    /**
     * @var Observer|MockObject
     */
    protected $eventObserver;

    protected function setUp(): void
    {
        $this->customerRepositoryMock = $this->createMock(
            CustomerRepositoryInterface::class
        );

        $this->loggerMock = $this->createMock(
            LoggerInterface::class
        );

        $this->helperDataMock = $this->createMock(
            Data::class
        );

        $this->event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder'])
            ->getMock();

        $this->eventObserver = $this->createMock(Observer::class);
        $this->eventObserver->expects($this->any())
            ->method('getEvent')
            ->willReturn($this->event);

        $this->observer = new NotifyNettermsUnapproved(
            $this->loggerMock,
            $this->customerRepositoryMock,
            $this->helperDataMock
        );

    }

    /**
     * @covers \Ziffity\Netterms\Observer\NotifyNettermsUnapproved::execute
     * @return void
     */
    public function testExecute()
    {
        $order = $this->getMockForAbstractClass(OrderInterface::class);
        $this->event->expects($this->any())->method('getOrder')->willReturn($order);

        $customerMock = $this->createMock(CustomerInterface::class);
        $customerId = $order->expects($this->any())->method('getCustomerId')->willReturnSelf();
        $order->expects($this->any())->method('getCustomerIsGuest')->willReturnSelf();
        $this->customerRepositoryMock->expects($this->any())->method('getById')->with($customerId)->willReturn($customerMock);

        $customerMock->expects($this->any())->method('getCustomAttribute')->with('net_terms_approved')->willReturnSelf();

        $this->helperDataMock->expects($this->any())->method('sentNonApprovedEmail')->willReturnSelf();
        $this->observer->execute($this->eventObserver);
    }
}

