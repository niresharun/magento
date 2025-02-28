<?php

namespace Ziffity\Shipping\Test\Unit\Controller\Adminhtml\Shipping\Profile;

use Ziffity\Shipping\Controller\Adminhtml\Shipping\Profile\Grid;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TestGrid extends TestCase
{

    /**
     * @var Grid
     */
    private $controller;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlMock;

    /**
     * @var (PageFactory&MockObject)|MockObject
     */
    private $resultPageFactoryMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $contextMock = $this->getMockBuilder(Context::class)
            ->setMethods(
                ['getRequest', 'getResponse', 'getResultFactory', 'getUrl']
            )->disableOriginalConstructor(
            )->getMock();

        $this->urlMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();

        $contextMock->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->urlMock);

        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->getMockBuilder(RequestInterface::class)
                ->getMockForAbstractClass());

        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->getMockBuilder(ResponseInterface::class)
                ->getMockForAbstractClass());

        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);

        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = (new ObjectManagerHelper($this))->getObject(
            Grid::class,
            [
                'context' => $contextMock,
                'pageFactory' => $this->resultPageFactoryMock
            ]
        );
    }

    /**
     * @covers \Ziffity\Shipping\Controller\Adminhtml\Shipping\Profile\Grid::execute
     * @return void
     */
    public function testExecute(): void
    {
        $viewResultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock->method('create')
            ->willReturn($viewResultPageMock);
        $actual = $this->controller->execute();
        $this->assertSame($viewResultPageMock, $actual);
    }
}
