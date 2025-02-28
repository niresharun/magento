<?php

declare(strict_types=1);

namespace Ziffity\CustomFrame\Test\Unit\Block\Adminhtml\QuantityClassification\Edit;

use Ziffity\CustomFrame\Block\Adminhtml\QuantityClassification\Edit\DeleteButton;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use Magento\Backend\Block\Widget\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** 
 * @covers \Ziffity\CustomFrame\Block\Adminhtml\QuantityClassification\Edit\DeleteButton
 */
class DeleteButtonTest extends TestCase
{

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilder;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var DeleteButton
     */
    private $deleteButton;

    /**
     * @var Registry|MockObject
     */
    protected $registry;

    /**
     * @var Context|MockObject
     */
    private $context;

    
    protected function setUp(): void
    {
        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();
        $this->urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);
        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->deleteButton = $objectManagerHelper->getObject(
            DeleteButton::class,
            [
                'context' => $this->context,
                'registry' => $this->registry,
                'urlBuilder' => $this->urlBuilder
            ]
        );
    }

    /**
     * Unit test for \Ziffity\CustomFrame\Test\Unit\Block\Adminhtml\QuantityClassification\Edit\DeleteButton::getButtonData() method
     */
    public function testGetButtonData()
    {
        $listId = 1;
        $currentListId = 1;
        $this->registry->expects($this->atleastOnce())->method('registry')->with('current_list_id')->willReturn($currentListId);
        $this->urlBuilder->expects($this->atLeastOnce())->method('getUrl')
            ->with(
                "*/lists/delete",
                ['id' => $listId]
            )->willReturn('url');

        $buttonData = $this->deleteButton->getButtonData();
        $this->assertEquals('Delete', (string)$buttonData['label']);
    }
}
