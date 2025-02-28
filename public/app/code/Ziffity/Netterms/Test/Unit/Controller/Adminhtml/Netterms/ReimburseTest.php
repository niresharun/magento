<?php
declare(strict_types=1);

namespace Ziffity\Netterms\Test\Unit\Controller\Adminhtml\Netterms;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ziffity\Netterms\Controller\Adminhtml\Netterms\Reimburse;
use Magento\Framework\Controller\Result\Json;

class ReimburseTest extends TestCase
{
    /**
     * @var Http|MockObject
     */
    private $request;

    /**
     * @var JsonFactory|MockObject
     */
    private $jsonFactoryMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var CustomerSession|MockObject
     */
    public $customerSessionMock;

    /**
     * @var Reimburse
     */
    public $controller;

    public function setUp(): void
    {
        /** @var Context|MockObject $context */
        $context = $this->createMock(Context::class);
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->method('getRequest')
            ->willReturn($this->request);
        $this->jsonFactoryMock = $this->createPartialMock(
            JsonFactory::class,
            ['create']
        );
        $this->customerRepositoryMock = $this->createMock(
            CustomerRepositoryInterface::class
        );
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->controller = new Reimburse(
            $context,
            $this->jsonFactoryMock,
            $this->loggerMock,
            $this->customerRepositoryMock
        );
    }

    /**
     * @covers \Ziffity\Netterms\Controller\Adminhtml\Netterms\Reimburse::execute
     * @return void
     */
    public function testExecute()
    {
        $customerId = 1;
        $this->request->expects($this->atLeastOnce())->method('getParam')->willReturn($customerId);
        $customerMock = $this->createMock(CustomerInterface::class);
        $this->customerRepositoryMock->expects($this->any())->method('getById')->with($customerId)->willReturn($customerMock);
        $json = $this->createMock(Json::class);
        $this->jsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($json);
        $json->expects($this->once())->method('setData')->willReturnSelf();
        $this->assertEquals($json, $this->controller->execute());
    }
}
