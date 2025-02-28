<?php

namespace Ziffity\Shipping\Test\Unit\Controller\Adminhtml\Oversize\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Message\ManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Ziffity\Shipping\Controller\Adminhtml\Oversize\Profile\Save;
use Ziffity\Shipping\Model\OversizeProfileCharge\ResourceModel\OversizeProfileCharge\CollectionFactory as CollectionFactory;
use Ziffity\Shipping\Model\OversizeProfileCharge\ResourceModel\ProfileCharge as SecondaryResourceModel;
use Ziffity\Shipping\Model\OversizeProfileCharge\ProfileChargeFactory as SecondaryModel;
use Ziffity\Shipping\Model\OversizeProfile\ResourceModel\OversizeProfile as ResourceModel;
use Ziffity\Shipping\Model\OversizeProfile\OversizeProfileFactory as Model;
use PHPUnit\Framework\TestCase;
use Ziffity\Shipping\Helper\Data;

class TestSave extends TestCase
{

    /**
     * @var (RedirectFactory&MockObject)|MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var (Context&MockObject)|MockObject
     */
    private $contextMock;

    /**
     * @var Save
     */
    private $controller;

    /**
     * @var RedirectInterface|(RedirectInterface&MockObject)|MockObject
     */
    private $redirect;

    /**
     * @var MockObject|SecondaryResourceModel|(SecondaryResourceModel&MockObject)
     */
    private $oversizeProfileResourceModel;

    /**
     * @var MockObject|SecondaryModel|(SecondaryModel&MockObject)
     */
    private $oversizeProfileModel;

    /**
     * @var MockObject|Model|(Model&MockObject)
     */
    private $oversizeModel;

    /**
     * @var MockObject|ResourceModel|(ResourceModel&MockObject)
     */
    private $oversizeResourceModel;

    /**
     * @var MockObject|Data|(Data&MockObject)
     */
    private $helperMock;

    /**
     * @var MockObject|(CollectionFactory&MockObject)
     */
    private $collectionFactory;

    /**
     * @var (RequestInterface&MockObject)|MockObject
     */
    private $requestMock;

    public function setUp(): void
    {
        //set referer url
        $this->redirect = $this->getMockForAbstractClass(RedirectInterface::class);
        //context class mock object
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->setMethods(
                ['getResultRedirectFactory','getMessageManager',
                    'getRequest','getRedirect']
            )->disableOriginalConstructor(
            )->getMock();
        //result redirect factory mock object
        $this->resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);
        $this->contextMock->expects($this->any())
            ->method('getRedirect')
            ->willReturn($this->redirect);
        //collection factory mock object
        $this->collectionFactory = $this->getMockBuilder(
            CollectionFactory::class
        )->setMethods(['create'])->disableOriginalConstructor()
            ->getMock();
        //message manager mock object
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        //request interface mock object
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        //oversize profile charge resource model mock object
        $this->oversizeProfileResourceModel = $this->createMock(SecondaryResourceModel::class);
        //oversize profile charge model mock object
        $this->oversizeProfileModel = $this->createMock(SecondaryModel::class);
        //oversize profile model mock object
        $this->oversizeModel = $this->createMock(Model::class);
        //oversize profile resource model mock object
        $this->oversizeResourceModel = $this->createMock(ResourceModel::class);
        //helper model mock object
        $this->helperMock = $this->createMock(Data::class);
        $this->controller = new Save(
            $this->contextMock,
            $this->oversizeModel,
            $this->oversizeResourceModel,
            $this->oversizeProfileModel,
            $this->oversizeProfileResourceModel,
            $this->collectionFactory,
            $this->helperMock
        );
    }

    /**
     * @covers \Ziffity\Shipping\Controller\Adminhtml\Oversize\Profile\Save::execute
     * @dataProvider testModelFactoryObjects
     * @param Object $mockModelFactory
     * @param string $param
     * @param string|array $path
     * @return void
     */
    public function testExecute($mockModelFactory, $param, $path)
    {
        $refererUrl = 'referer_url';
        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirect->expects($this->any())
            ->method('setPath')
            ->with($path[0], $path[1])
            ->willReturnSelf();
        $this->resultRedirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($resultRedirect);
        if ($param == "back") {
            $this->requestMock->expects($this->once())
                ->method('getParam')
                ->willReturn('close');
        }
        if ($param!=="back") {
            $this->requestMock->expects($this->once())
                ->method('getParam')
                ->willReturn(null);
        }
        if ($mockModelFactory==null && $param!=="back") {
            $this->redirect->expects($this->once())
                ->method('getRefererUrl')
                ->willReturn($refererUrl);
        }
        $this->helperMock->expects($this->any())
            ->method('saveLogic')
            ->willReturn($mockModelFactory);
        $actual = $this->controller->execute();
        $this->assertSame($resultRedirect, $actual);
    }

    public function testModelFactoryObjects()
    {
        $profileObj = new DataObject(['profile_id'=>'1']);
        return [
            ['mockModelFactory'=>$profileObj,'param'=>'null',
                'path'=>['*/*/edit',['profile_id'=>$profileObj
                    ->getProfileId(),'_current'=>true]]],
            ['mockModelFactory'=> null, 'param'=>'back',
                'path'=>['*/oversize_profile/grid',[]]],
            ['mockModelFactory'=> null, 'param'=>'null',
                'path'=>['referer_url',[]]]
        ];
    }
}
