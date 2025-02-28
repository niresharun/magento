<?php

namespace Ziffity\SavedDesigns\Test\Unit\Controller\Save;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Ziffity\SavedDesigns\Model\SavedDesignsFactory;
use Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns as SavedDesignsResourceModel;
use PHPUnit\Framework\TestCase;
use Ziffity\SavedDesigns\Helper\Data as Helper;
use Psr\Log\LoggerInterface;
use Ziffity\SavedDesigns\Controller\Save\Product;

class ProductTest extends TestCase
{

    /**
     * @var StoreManagerInterface|MockObject
     */
    public $storeManagerInterfaceMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    public $productRepositoryMock;

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
     * @var ResponseInterface|MockObject
     */
    public $responseMock;

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
     * @var Helper|MockObject
     */
    public $helperMock;

    /**
     * @var Product|MockObject
     */
    public $productController;

    public function setUp(): void
    {
        $this->storeManagerInterfaceMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->validatorMock = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->requestMock = $this->getMockBuilder(
                RequestInterface::class
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock = $this->createMock(Json::class);
        $this->managerInterfaceMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->responseMock = $this->getMockForAbstractClass(
            ResponseInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['representJson']
        );
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->modelFactoryMock = $this->createMock(SavedDesignsFactory::class);
        $this->resourceModelMock = $this->createMock(SavedDesignsResourceModel::class);
        $this->helperMock = $this->createMock(Helper::class);
        $objectManagerHelper = new ObjectManager($this);
        $this->productController = $objectManagerHelper->getObject(
            Product::class,
            [
                'storeManager' => $this->storeManagerInterfaceMock,
                'formKeyValidator' => $this->validatorMock,
                'productRepository' => $this->productRepositoryMock,
                'request' => $this->requestMock,
                'serializer' => $this->serializerMock,
                'messageManager' => $this->managerInterfaceMock,
                'customerSession' => $this->customerSessionMock,
                'response' => $this->responseMock,
                'logger' => $this->loggerMock,
                'savedDesignFactory' => $this->modelFactoryMock,
                'savedDesignsResourceModel' => $this->resourceModelMock,
                'helperData' => $this->helperMock
            ]
        );
    }

    /**
     * @covers \Ziffity\SavedDesigns\Controller\Save\Product::execute
     * @return void
     */
    public function testExecute()
    {
        $this->validatorMock->expects($this->once())->method('validate')->with($this->requestMock)->willReturn(false);

        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->willReturnSelf();
        $this->managerInterfaceMock->expects($this->once())->method('addErrorMessage');
        $this->serializerMock->expects($this->once())->method('serialize')->willReturnSelf();
        $this->productController->execute();
    }
}
