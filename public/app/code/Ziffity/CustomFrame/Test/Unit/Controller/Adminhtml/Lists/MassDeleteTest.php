<?php

declare(strict_types=1);

namespace Ziffity\CustomFrame\Test\Unit\Controller\Adminhtml\Lists;

use Ziffity\CustomFrame\Controller\Adminhtml\Lists\MassDelete;
use Ziffity\CustomFrame\Model\ResourceModel\QuantityClassification\Collection;
use Ziffity\CustomFrame\Model\ResourceModel\QuantityClassification\CollectionFactory;
use Ziffity\CustomFrame\Test\Unit\Controller\Adminhtml\AbstractMassActionTest;
use PHPUnit\Framework\MockObject\MockObject;

class MassDeleteTest extends AbstractMassActionTest
{
    /**
     * @var MassDelete
     */
    protected $massDeleteController;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    protected $listCollectionMock;

    
    protected function setUp(): void
    {
        parent::setUp();

        $this->collectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->listCollectionMock = $this->createMock(Collection::class);

        $this->massDeleteController = $this->objectManager->getObject(
            MassDelete::class,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->collectionFactoryMock
            ]
        );
    }

    /**
     * @covers MassDelete action
     */
    public function testMassDeleteAction()
    {
        $deletedListsCount = 2;

        $collection = [
            $this->getListMock(),
            $this->getListMock()
        ];

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->listCollectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->listCollectionMock)
            ->willReturn($this->listCollectionMock);

        $this->listCollectionMock->expects($this->once())->method('getSize')->willReturn($deletedListsCount);
        $this->listCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($collection));

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 list(s) have been deleted.', $deletedListsCount));
        $this->messageManagerMock->expects($this->never())->method('addErrorMessage');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/index/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->massDeleteController->execute());
    }

    /**
     * Create List Collection Mock
     *
     * @return \Ziffity\CustomFrame\Model\ResourceModel\QuantityClassification\Collection|MockObject
     */
    protected function getListMock()
    {
        $listMock = $this->getMockBuilder(Collection::class)
            ->addMethods(['delete'])
            ->disableOriginalConstructor()
            ->getMock();
        $listMock->expects($this->once())->method('delete')->willReturn(true);

        return $listMock;
    }
}
