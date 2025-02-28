<?php

namespace Ziffity\SavedDesigns\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\StoreManagerInterface;
use Ziffity\SavedDesigns\Helper\Data;
use Magento\Framework\Filesystem\Io\File as IoFileSystem;
use Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns\CollectionFactory;
use Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns\Collection;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var IoFileSystem|MockObject
     */
    private $fileSystemIoMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystemMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $className = Data::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var Context $context */
        $contextMock = $arguments['context'];
        $this->scopeConfig = $contextMock->getScopeConfig();
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->fileSystemIoMock = $this->createMock(IoFileSystem::class);
        $this->collectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->scopeConfig = $contextMock->getScopeConfig();
        $this->filesystemMock = $this->createMock(Filesystem::class);

        $this->helperData = new Data(
            $contextMock,
            $this->fileSystemIoMock,
            $this->collectionFactoryMock,
            $this->filesystemMock
        );
    }

    /**
     * @covers \Ziffity\SavedDesigns\Helper\Data::getSaveLimitScope
     * @dataProvider getSaveLimitScopeDataProvider
     * @return void
     */
    public function testGetSaveLimitScope($data)
    {
        $this->scopeConfig->expects($this->any())->method('getValue')->with(
            'saved_design/general/max_save_limit',
            ScopeInterface::SCOPE_STORE
        )->willReturn($data);
        $this->assertIsInt($this->helperData->getSaveLimitScope());
    }

    /**
     * Data provider for testGetSaveLimitScope
     * @return array
     */
    public function getSaveLimitScopeDataProvider()
    {
        return [
            [1],
            [2]
        ];
    }
}
