<?php

namespace Ziffity\Shipping\Test\Unit\Controller\Adminhtml\Shipping\Profile;

use Ziffity\Shipping\Controller\Adminhtml\Shipping\Profile\Add;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TestAdd extends TestCase
{

    /**
     * @var Add
     */
    private $controller;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlMock;

    /**
     * @var (RedirectFactory&MockObject)|MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var (Context&MockObject)|MockObject
     */
    private $contextMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->setMethods(
                ['getRequest', 'getResponse', 'getUrl','getResultRedirectFactory']
            )->disableOriginalConstructor(
            )->getMock();

        $this->urlMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->urlMock);

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->getMockBuilder(RequestInterface::class)
                ->getMockForAbstractClass());

        $this->contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->getMockBuilder(ResponseInterface::class)
                ->getMockForAbstractClass());

        $this->resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->controller = (new ObjectManagerHelper($this))->getObject(
            Add::class,
            [
                'context' => $this->contextMock
            ]
        );
    }

    /**
     * @covers \Ziffity\Shipping\Controller\Adminhtml\Shipping\Profile\Add::execute
     * @return void
     */
    public function testExecute(): void
    {
        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirect->expects($this->any())->method('setPath')
            ->with('shipping/shipping_profile/edit')
            ->willReturnSelf();
        $this->resultRedirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($resultRedirect);
        $actual = $this->controller->execute();
        $this->assertSame($resultRedirect, $actual);
    }
}
