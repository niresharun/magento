<?php

namespace Ziffity\Shipping\Test\Unit\Controller\Adminhtml\Oversize\Profile;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Ziffity\Shipping\Model\OversizeProfile\ResourceModel\OversizeProfile\Collection as CollectionModel;
use Ziffity\Shipping\Model\OversizeProfile\ResourceModel\OversizeProfile\CollectionFactory as Collection;
use Magento\Framework\Message\ManagerInterface;
use Ziffity\Shipping\Controller\Adminhtml\Oversize\Profile\MassDelete;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ArrayIterator;

class TestMassDelete extends TestCase
{

    /**
     * @var (Filter&MockObject)|MockObject
     */
    private $filter;

    /**
     * @var MassDelete
     */
    private $controller;

    /**
     * @var (RedirectFactory&MockObject)|MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var (Context&MockObject)|MockObject
     */
    private $contextMock;

    /**
     * @var ManagerInterface|(ManagerInterface&MockObject)|MockObject
     */
    private $messageManagerMock;

    /**
     * @var (RequestInterface&MockObject)|MockObject
     */
    private $requestMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->setMethods(
                ['getResultRedirectFactory','getMessageManager',
                    'getRequest']
            )->disableOriginalConstructor(
            )->getMock();

        $this->resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->filter = $this->getMockBuilder(Filter::class)
            ->setMethods(['getCollection'])->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactory = $this->getMockBuilder(
            Collection::class
        )->setMethods(['create'])->disableOriginalConstructor()
            ->getMock();

        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);

        $this->filter = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCollection'])
            ->getMock();

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->controller = (new ObjectManagerHelper($this))->getObject(
            MassDelete::class,
            [
                'context' => $this->contextMock,
                'collection'=> $this->collectionFactory,
                'filter'=> $this->filter
            ]
        );
    }

    /**
     * Testing the function in delete controller.
     *
     * @dataProvider dataProviderForExecute
     * @covers \Ziffity\Shipping\Controller\Adminhtml\Oversize\Profile\MassDelete::execute
     * @return void
     */
    public function testExecute($collectionSize): void
    {
        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirect->expects($this->any())
            ->method('setPath')
            ->with('*/*/grid')
            ->willReturnSelf();
        $this->resultRedirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($resultRedirect);
            $this->messageManagerMock->expects($this->any())
                ->method('addSuccessMessage')
                ->with(__('A total of %1 element(s) have been deleted.', $collectionSize));
        $this->messageManagerMock->expects($this->never())
            ->method('addErrorMessage');

        //collection and filter logic
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('filters')
            ->willReturn(['placeholder' => true]);
        $this->requestMock->expects($this->any())
            ->method('getParams')->willReturn(
                [
                'namespace' => 'oversize_profile_listing',
                'exclude' => true,
                'filters' => ['placeholder' => true]
                ]
            );
        $dataObject = $this->getMockBuilder(DataObject::class)
            ->addMethods(['delete'])
            ->onlyMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $dataObject->expects($this->any())
            ->method('getData')
            ->willReturnMap([
                [null, null, 10],
                ['name', null, 'test'],
            ]);
        $collection = $this->getMockBuilder(CollectionModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->any())->method('getAllIds')->willReturn([1, 2, 3]);
        $collection->expects($this->any())->method('getSize')->willReturn($collectionSize);
        $collection->expects($this->any())->method('getIterator')->willReturn(new ArrayIterator([$dataObject]));
        $this->filter->expects($this->any())->method('getCollection')->with($collection)->willReturn($collection);
        $this->collectionFactory->expects($this->any())->method('create')->willReturn($collection);
        //collection and filter logic ends here

        $actual = $this->controller->execute();
        $this->assertSame($resultRedirect, $actual);
    }

    /**
     * DataProvider for execute function.
     *
     * @return array[]
     */
    public function dataProviderForExecute()
    {
        return [
            ['collectionSize'=>1]
        ];
    }
}
