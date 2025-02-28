<?php
declare(strict_types=1);

namespace Ziffity\SavedDesigns\Test\Unit\Controller\Lists;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Ziffity\SavedDesigns\Model\SavedDesignsAuthenticator;
use Ziffity\SavedDesigns\Model\SavedDesignsFactory;
use Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns as SavedDesignsResourceModel;
use PHPUnit\Framework\MockObject\MockObject;
use Ziffity\SavedDesigns\Controller\Lists\Delete;
use PHPUnit\Framework\TestCase;

class DeleteTest  extends TestCase
{

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var JsonFactory|MockObject
     */
    protected $resultJsonFactoryMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var SavedDesignsFactory|MockObject
     */
    protected $savedDesignFactoryMock;

    /**
     * @var SavedDesignsResourceModel|MockObject
     */
    protected $savedDesignsResourceModelMock;

    /**
     * @var SavedDesignsAuthenticator|MockObject
     */
    protected $savedDesignsAuthenticatorMock;

    /**
     * @var Json|MockObject
     */
    private $resultJsonMock;

    /**
     * @var Delete|MockObject
     */
    public $deleteController;


    public function setUp(): void
    {
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->resultJsonFactoryMock = $this->createMock(JsonFactory::class);
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->savedDesignFactoryMock = $this->createMock(SavedDesignsFactory::class);
        $this->savedDesignsResourceModelMock = $this->createMock(SavedDesignsResourceModel::class);
        $this->savedDesignsAuthenticatorMock = $this->createMock(SavedDesignsAuthenticator::class);
        $this->resultJsonMock = $this->createMock(Json::class);

        $this->deleteController = new Delete(
            $this->requestMock,
            $this->resultJsonFactoryMock,
            $this->messageManagerMock,
            $this->loggerMock,
            $this->savedDesignFactoryMock,
            $this->savedDesignsResourceModelMock,
            $this->savedDesignsAuthenticatorMock
        );
    }

    /**
     * @covers \Ziffity\SavedDesigns\Controller\lists\Delete::execute
     * @return void
     */
    public function testExecute()
    {
        $id = 1;
        $this->resultJsonFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultJsonMock);
        $this->requestMock->expects($this->any())->method('getParams')->willReturn(['id' => $id]);

        $this->savedDesignsAuthenticatorMock->expects($this->any())->method('isAllowedAction');

        $savedDesignsMock = $this->createMock(\Ziffity\SavedDesigns\Model\SavedDesigns::class);
        $this->savedDesignFactoryMock->expects($this->any())->method('create')->will($this->returnValue($savedDesignsMock));

        $entity = $savedDesignsMock->expects($this->any())
            ->method('load')
            ->with($id)
            ->willReturnSelf();

        $this->savedDesignsResourceModelMock->expects($this->any())
            ->method('delete')
            ->with($entity);

        $this->messageManagerMock->expects($this->any())->method('addErrorMessage');

        $this->assertEquals($this->resultJsonMock, $this->deleteController->execute());
    }
}
