<?php

namespace Ziffity\Shipping\Test\Unit\Controller\Adminhtml\Shipping\Profile;

use Magento\Framework\Message\ManagerInterface;
use Ziffity\Shipping\Model\ShippingProfile\ResourceModel\ShippingProfile as ResourceModel;
use Ziffity\Shipping\Model\ShippingProfile\ShippingProfile;
use Ziffity\Shipping\Model\ShippingProfile\ShippingProfileFactory as Model;
use Ziffity\Shipping\Controller\Adminhtml\Shipping\Profile\Delete;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TestDelete extends TestCase
{

    /**
     * @var MockObject|Model|(Model&MockObject)
     */
    private $mockModel;

    /**
     * @var MockObject|ResourceModel|(ResourceModel&MockObject)
     */
    private $mockResourceModel;

    /**
     * @var Delete
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
     * @var (RequestInterface&MockObject)|MockObject
     */
    private $requestMock;

    /**
     * @var ManagerInterface|(ManagerInterface&MockObject)|MockObject
     */
    private $messageManagerMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->setMethods(
                ['getRequest', 'getResponse', 'getUrl',
                    'getResultRedirectFactory','getMessageManager']
            )->disableOriginalConstructor(
            )->getMock();

        $this->urlMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->urlMock);

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);

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

        $this->mockModel = $this->createMock(Model::class);

        $this->mockResourceModel = $this->createMock(ResourceModel::class);

        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);

        $this->controller = (new ObjectManagerHelper($this))->getObject(
            Delete::class,
            [
                'context' => $this->contextMock,
                'model'=> $this->mockModel,
                'resourceModel'=> $this->mockResourceModel
            ]
        );
    }

    /**
     * Testing the function in delete controller.
     *
     * @dataProvider testParamDataProvider
     * @covers \Ziffity\Shipping\Controller\Adminhtml\Shipping\Profile\Delete::execute
     * @return void
     */
    public function testExecute($profileId, $profileName): void
    {
        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirect->expects($this->any())->method('setPath')
            ->with('*/shipping_profile/grid')
            ->willReturnSelf();
        $this->resultRedirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($resultRedirect);
        if ($profileId) {
            $this->requestMock->expects($this->once())
                ->method('getParam')
                ->willReturn($profileId);
            $model = $this->getMockBuilder(ShippingProfile::class)
                ->disableOriginalConstructor()
                ->setMethods(['getProfileName'])
                ->getMock();
            $model->expects($this->any())
                ->method('getProfileName')
                ->willReturn($profileName);
            $this->mockModel->expects($this->once())->method('create')
                ->willReturn($model);
            $this->mockResourceModel->expects($this->once())
                ->method('load')
                ->with($model, $profileId, "profile_id")
                ->willReturn($model);
            $this->mockResourceModel->expects($this->once())
                ->method('delete')
                ->willReturn($model);
            $this->messageManagerMock->expects($this->any())
                ->method('addSuccessMessage')
                ->with(__('Shipping Profile %name Deleted', $profileName));
        }
        if ($profileId == null) {
            $this->messageManagerMock->expects($this->any())
                ->method('addErrorMessage')
                ->with(__('Oversize Profile with id %1 not found', $profileName));
        }
        $actual = $this->controller->execute();
        $this->assertSame($resultRedirect, $actual);
    }

    /**
     * @return array
     */
    public function testParamDataProvider()
    {
        return [
            ['profile_id'=>"1",'profile_name'=>"Test1"],
            ['profile_id'=>null,"profile_name"=>null],
            ['profile_id'=>'not_found',"profile_name"=>"no_name"]
        ];
    }
}
