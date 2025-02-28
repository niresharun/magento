<?php

namespace Ziffity\CustomFrame\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
use Ziffity\CustomFrame\Ui\DataProvider\Product\Form\Modifier\Composite;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class TestComposite extends TestCase
{

    /**
     * @var Composite
     */
    public $compositeMock;

    /**
     * @var (LocatorInterface&MockObject)|MockObject
     */
    public $locatorMock;

    /**
     * @var ObjectManagerInterface
     */
    public $objectManagerMock;

    /**
     * @var (ProductInterface&MockObject)|MockObject
     */
    public $productMock;

    /**
     * @var MockObject|ProductOptionRepositoryInterface|(ProductOptionRepositoryInterface&MockObject)
     */
    public $optionRepositoryMock;

    /**
     * @var ProductRepositoryInterface|(ProductRepositoryInterface&MockObject)|MockObject
     */
    public $productRepositoryMock;

    /**
     * @var string
     */
    protected $modifierClass;

    protected $modifiedMeta;

    protected $meta;

    public function setUp(): void
    {
        $this->meta = ['some_meta'];
        $this->modifiedMeta = ['modified_meta'];
        $this->modifierClass = 'SomeClass';
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->productMock   = $this->getMockBuilder(ProductInterface::class)
            ->addMethods(['getStoreId'])->getMockForAbstractClass();
        $this->locatorMock = $this->getMockBuilder(LocatorInterface::class)
            ->onlyMethods(['getProduct'])->getMockForAbstractClass();
        $this->locatorMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->optionRepositoryMock  = $this
            ->getMockForAbstractClass(\Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface::class);
        $this->productRepositoryMock = $this
            ->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->compositeMock         = $this->objectManagerHelper->getObject(
            Composite::class,
            [
                'locator'                 => $this->locatorMock,
                'objectManager'           => $this->objectManagerMock,
                'optionsRepository' => $this->optionRepositoryMock,
                'productRepository'       => $this->productRepositoryMock,
                'modifiers' => ['mod' => $this->modifierClass]
            ]
        );
    }


    /**
     * @return void
     */
    public function testWithoutModifierData()
    {
        $this->compositeMock         = $this->objectManagerHelper->getObject(
            Composite::class,
            [
                'locator'                 => $this->locatorMock,
                'objectManager'           => $this->objectManagerMock,
                'optionsRepository' => $this->optionRepositoryMock,
                'productRepository'       => $this->productRepositoryMock,
                'modifiers' => []
            ]
        );
        $this->assertEquals($this->meta, $this->compositeMock->modifyMeta($this->meta));
    }

    public function testModifyMetaWithoutModifiers()
    {
        $this->compositeMock = $this->objectManagerHelper->getObject(
            Composite::class,
            [
                'locator' => $this->locatorMock,
                'objectManager' => $this->objectManagerMock,
                'modifiers' => []
            ]
        );
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('customframe');
        $this->objectManagerMock->expects($this->never())
            ->method('get');

        $this->assertSame($this->meta, $this->compositeMock->modifyMeta($this->meta));
    }

    /**
     * @return void
     */
    public function testModifyMetaCustomFrameProduct()
    {
        /** @var ModifierInterface|MockObject $modifierMock */
        $modifierMock = $this->getMockForAbstractClass(ModifierInterface::class);
        $modifierMock->expects($this->once())
            ->method('modifyMeta')
            ->with($this->meta)
            ->willReturn($this->modifiedMeta);

        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('customframe');
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->modifierClass)
            ->willReturn($modifierMock);

        $this->assertSame($this->modifiedMeta, $this->compositeMock->modifyMeta($this->meta));
    }

    /**
     * @return void
     */
    public function testModifyMetaNonCustomFrameProduct()
    {
        /** @var ModifierInterface|MockObject $modifierMock */
        $modifierMock = $this->getMockForAbstractClass(ModifierInterface::class);
        $modifierMock->expects($this->never())
            ->method('modifyMeta');

        $this->productMock->expects($this->any())
            ->method('getTypeId')
            ->willReturn('someBundleProduct');
        $this->objectManagerMock->expects($this->never())
            ->method('get');

        $this->assertSame($this->meta, $this->compositeMock->modifyMeta($this->meta));
    }

    /**
     * @return void
     */
    public function testModifyMetaWithException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Type "SomeClass" is not an instance of Magento\\Ui\\DataProvider\\Modifier\\ModifierInterface'
        );

        /** @var \Exception|MockObject $modifierMock */
        $modifierMock = $this->getMockBuilder(\Exception::class)->addMethods(['modifyMeta'])
            ->disableOriginalConstructor()
            ->getMock();
        $modifierMock->expects($this->never())
            ->method('modifyMeta');

        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('customframe');
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->modifierClass)
            ->willReturn($modifierMock);

        $this->compositeMock->modifyMeta($this->meta);
    }
}
