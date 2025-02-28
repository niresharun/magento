<?php

declare(strict_types=1);

namespace Ziffity\CustomFrame\Test\Unit\Controller\Adminhtml\Lists;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Ziffity\CustomFrame\Controller\Adminhtml\Lists\Save;
use Ziffity\CustomFrame\Model\QuantityClassificationFactory;
use Ziffity\CustomFrame\Model\QuantityClassification;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use \Magento\Framework\Serialize\Serializer\Json;

/**
 * @covers \Ziffity\CustomFrame\Controller\Adminhtml\Lists\Save
 */
class SaveTest extends TestCase
{
    /**
     * @var Save
     */
    protected $saveController;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var ObjectManager|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var QuantityClassififcationFactory|MockObject
     */
    protected $listFactoryMock;

    /**
     * @var QuantityClassififcation|MockObject
     */
    protected $listMock;

    /**
     * @var Json|MockObject
     */
    protected $serializerMock;

    /**
     * @var int
     */
    protected $listId = 1;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getPostValue'])
            ->getMockForAbstractClass();

        $this->listFactoryMock = $this->getMockBuilder(QuantityClassificationFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->listMock = $this->getMockBuilder(QuantityClassification::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'save', 'setListName', 'setClassification'])
            ->getMock();
        
        $this->listFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->listMock);

        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->onlyMethods(['serialize'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            RedirectFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->contextMock = $this->createMock(Context::class);

        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->saveController = $this->objectManager->getObject(
            Save::class,
            [
                'context' => $this->contextMock,
                'listFactory' => $this->listFactoryMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * @covers Save Action
     */
    public function testSaveAction()
    {
        $data = [
            'list_name' => 'list1',
            '{"size_from":"10","size_to":"20","qty":"2","record_id":"0"}'
        ];

        $listData['quantity_classification'] = $data;
        $this->requestMock
            ->method('getParam')
            ->withConsecutive(
                ['back', false],
                ['quantity_classififcation']
            )
            ->willReturnOnConsecutiveCalls(
                true,
                $listData
            );

        $this->requestMock->expects($this->once())->method('getPostValue')->willReturn($listData);

        $this->listFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->listMock);
        $this->listMock->expects($this->once())
            ->method('setListName')
            ->with($listData['quantity_classification']['list_name'])
            ->willReturn(true);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('List has been successfully saved.'));
        $this->messageManagerMock->expects($this->never())
            ->method('addErrorMessage');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/index/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->saveController->execute());
    }

    /**
     * @covers Save with existing object
     */
    public function testSaveExistingObject()
    {
        $listId = 1;
        $data = [
            'id' => '1',
            'list_name' => 'list1',
            '{"size_from":"10","size_to":"20","qty":"2","record_id":"0"}'
        ];

        $listData['quantity_classification'] = $data;
        $this->requestMock
            ->method('getParam')
            ->withConsecutive(
                ['back', false],
                ['quantity_classififcation']
            )
            ->willReturnOnConsecutiveCalls(
                true,
                $listData
            );

        $this->requestMock->expects($this->once())->method('getPostValue')->willReturn($listData);

        $this->listFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->listMock);

        $this->listMock->expects($this->once())
            ->method('load')
            ->with($listId)
            ->willReturn($this->listMock);
        $this->listMock->expects($this->once())
            ->method('setListName')
            ->with($listData['quantity_classification']['list_name'])
            ->willReturn(true);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('List has been successfully saved.'));
        $this->messageManagerMock->expects($this->never())
            ->method('addErrorMessage');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/index/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->saveController->execute());
    }  
}
