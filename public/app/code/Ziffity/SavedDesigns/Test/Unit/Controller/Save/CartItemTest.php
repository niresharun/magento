<?php

namespace Ziffity\SavedDesigns\Test\Unit\Controller\Save;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Ziffity\SavedDesigns\Model\SavedDesignsFactory;
use Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns as SavedDesignsResourceModel;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ziffity\SavedDesigns\Controller\Save\CartItem;

class CartItemTest extends TestCase
{

    /**
     * @var StoreManagerInterface|MockObject
     */
    public $storeManagerInterfaceMock;

    /**
     * @var ManagerInterface|MockObject
     */
    public $managerInterfaceMock;

    /**
     * @var Validator|MockObject
     */
    public $validatorMock;

    /**
     * @var RequestInterface|MockObject
     */
    public $requestMock;

    /**
     * @var CustomerSession|MockObject
     */
    public $customerSessionMock;

    /**
     * @var SavedDesignsFactory|MockObject
     */
    public $modelFactoryMock;

    /**
     * @var ResultFactory|MockObject
     */
    public $resultFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    public $loggerMock;

    /**
     * @var Json|MockObject
     */
    public $serializerMock;

    /**
     * @var SavedDesignsResourceModel|MockObject
     */
    public $resourceModelMock;

    /**
     * @var CheckoutSession|MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var CartItem|MockObject
     */
    public $cartItemcontroller;

    public function setUp(): void
    {
        $this->storeManagerInterfaceMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->validatorMock = $this->createMock(Validator::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->serializerMock = $this->createMock(Json::class);
        $this->managerInterfaceMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $quoteMock = $this->createMock(Quote::class);
        $this->checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $quoteMock->expects($this->any())->method('getItemById')->willReturn([]);
        $this->checkoutSessionMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);

        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->modelFactoryMock = $this->createMock(SavedDesignsFactory::class);
        $this->resourceModelMock = $this->createMock(SavedDesignsResourceModel::class);

        $this->cartItemcontroller = new CartItem(
            $this->storeManagerInterfaceMock,
            $this->validatorMock,
            $this->requestMock,
            $this->serializerMock,
            $this->managerInterfaceMock,
            $this->customerSessionMock,
            $this->checkoutSessionMock,
            $this->resultFactoryMock,
            $this->loggerMock,
            $this->modelFactoryMock,
            $this->resourceModelMock,
        );
    }

    /**
     * @covers \Ziffity\SavedDesigns\Controller\Save\CartItem::execute
     * @return void
     */
    public function testExecute()
    {
        $redirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $path = 'checkout/cart';

        $this->validatorMock->expects($this->once())->method('validate')->with($this->requestMock)->willReturn(false);
        $this->managerInterfaceMock->expects($this->once())->method('addErrorMessage');
        $this->resultFactoryMock->expects($this->once())->method('create')->willReturn($redirect);
        $redirect->expects($this->once())->method('setPath')->with($path)->willReturnSelf();
        $this->assertEquals($redirect, $this->cartItemcontroller->execute());
    }
}
