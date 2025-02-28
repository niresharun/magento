<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ziffity\CustomFrame\Test\Unit\Model\CustomFrame;

use Exception;
use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Ziffity\CustomFrame\Model\CustomFrame\LinkManagement;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\ResourceModel\Bundle;
use Magento\Bundle\Model\ResourceModel\BundleFactory;
use Magento\Bundle\Model\ResourceModel\Option\Collection as OptionCollection;
use Magento\Bundle\Model\ResourceModel\Option\CollectionFactory as OptionCollectionFactory;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as SelectionCollection;
use Magento\Bundle\Model\Selection;
use Magento\Bundle\Model\SelectionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Ziffity\CustomFrame\Model\Product\Type;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Bundle\Model\LinkManagement
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkManagementTest extends TestCase
{
    /**
     * @var LinkManagement
     */
    private $model;

    /**
     * @var ProductRepository|MockObject
     */
    private $productRepository;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var LinkInterfaceFactory|MockObject
     */
    private $linkFactory;

    /**
     * @var Type|MockObject
     */
    private $productType;

    /**
     * @var OptionCollection|MockObject
     */
    private $optionCollection;

    /**
     * @var SelectionCollection|MockObject
     */
    private $selectionCollection;

    /**
     * @var Option|MockObject
     */
    private $option;

    /**
     * @var SelectionFactory|MockObject
     */
    private $bundleSelectionMock;

    /**
     * @var BundleFactory|MockObject
     */
    private $bundleFactoryMock;

    /**
     * @var OptionCollectionFactory|MockObject
     */
    private $optionCollectionFactoryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var LinkInterface|MockObject
     */
    private $link;

    /**
     * @var MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    /**
     * @var EntityMetadata|MockObject
     */
    private $metadataMock;

    /**
     * @var int
     */
    private $storeId = 2;

    /**
     * @var array
     */
    private $optionIds = [1, 2, 3];

    /**
     * @var string
     */
    private $linkField = 'product_id';

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->productRepository = $this->getMockBuilder(ProductRepository::class)
            ->onlyMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productType = $this->getMockBuilder(\Magento\Bundle\Model\Product\Type::class)
            ->onlyMethods(['getOptionsCollection', 'setStoreFilter', 'getSelectionsCollection', 'getOptionsIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->option = $this->getMockBuilder(Option::class)
            ->onlyMethods(['getOptionId', '__wakeup'])
            ->addMethods(['getSelections'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionCollection = $this->getMockBuilder(OptionCollection::class)
            ->onlyMethods(['appendSelections'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectionCollection = $this->getMockBuilder(
            SelectionCollection::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->product = $this->getMockBuilder(Product::class)
            ->onlyMethods(['getTypeInstance', 'getStoreId', 'getTypeId', '__wakeup', 'getId', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->link = $this->getMockBuilder(LinkInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->linkFactory = $this->getMockBuilder(LinkInterfaceFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->bundleSelectionMock = $this->createPartialMock(
            SelectionFactory::class,
            ['create']
        );
        $this->bundleFactoryMock = $this->createPartialMock(
            BundleFactory::class,
            ['create']
        );
        $this->optionCollectionFactoryMock = $this->createPartialMock(
            OptionCollectionFactory::class,
            ['create']
        );
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataMock = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($this->metadataMock);

        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $helper->getObject(
            LinkManagement::class,
            [
                'productRepository' => $this->productRepository,
                'linkFactory' => $this->linkFactory,
                'bundleFactory' => $this->bundleFactoryMock,
                'bundleSelection' => $this->bundleSelectionMock,
                'optionCollection' => $this->optionCollectionFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'metadataPool' => $this->metadataPoolMock
            ]
        );
    }

    /**
     * @covers \Ziffity\CustomFrame\Model\CustomFrame\LinkManagement::getChildren
     * @return void
     */
    public function testGetChildren(): void
    {
        $productSku = 'productSku';

        $this->getOptions();

        $this->productRepository->method('get')
            ->with($productSku)
            ->willReturn($this->product);

        $this->product->expects($this->once())
            ->method('getTypeId')
            ->willReturn('customframe');

        $this->productType->expects($this->once())
            ->method('setStoreFilter')
            ->with(
                $this->storeId,
                $this->product
            );
        $this->productType->expects($this->once())
            ->method('getSelectionsCollection')
            ->with(
                $this->optionIds,
                $this->product
            )
            ->willReturn($this->selectionCollection);
        $this->productType->expects($this->once())
            ->method('getOptionsIds')
            ->with($this->product)
            ->willReturn($this->optionIds);

        $this->optionCollection->expects($this->once())
            ->method('appendSelections')
            ->with($this->selectionCollection)
            ->willReturn([$this->option]);

        $this->option->method('getSelections')
            ->willReturn([$this->product]);
        $this->product->method('getData')
            ->willReturn([]);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($this->link, $this->anything(), LinkInterface::class)
            ->willReturnSelf();
        $this->link->expects($this->once())->method('setIsDefault')->willReturnSelf();
        $this->link->expects($this->once())->method('setQty')->willReturnSelf();
        $this->link->expects($this->once())->method('setCanChangeQuantity')->willReturnSelf();
        $this->link->expects($this->once())->method('setPrice')->willReturnSelf();
        $this->link->expects($this->once())->method('setPriceType')->willReturnSelf();
        $this->link->expects($this->once())->method('setId')->willReturnSelf();
        $this->linkFactory->expects($this->once())->method('create')->willReturn($this->link);

        $this->assertEquals([$this->link], $this->model->getChildren($productSku));
    }

    /**
     * @return void
     */
    public function testAddChild(): void
    {
        $productLink = $this->getMockBuilder(LinkInterface::class)
            ->onlyMethods(['getSku', 'getOptionId'])
            ->addMethods(['getSelectionId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productLink->method('getSku')->willReturn('linked_product_sku');
        $productLink->method('getOptionId')->willReturn(1);
        $productLink->method('getSelectionId')->willReturn(1);

        $this->metadataMock->expects($this->exactly(2))->method('getLinkField')->willReturn($this->linkField);
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())->method('getTypeId')->willReturn(Type::TYPE_CODE);
        $productMock
            ->method('getData')
            ->with($this->linkField)
            ->willReturn($this->linkField);

        $linkedProductMock = $this->createMock(Product::class);
        $linkedProductMock->method('getId')->willReturn(13);
        $linkedProductMock->expects($this->once())->method('isComposite')->willReturn(false);
        $this->productRepository
            ->expects($this->once())
            ->method('get')
            ->with('linked_product_sku')
            ->willReturn($linkedProductMock);

        $store = $this->createMock(Store::class);
        $this->storeManagerMock->method('getStore')->willReturn($store);
        $store->method('getId')->willReturn(0);

        $option = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getId')->willReturn(1);

        $optionsCollectionMock = $this->createMock(OptionCollection::class);
        $optionsCollectionMock->expects($this->once())
            ->method('setIdFilter')
            ->with(1)
            ->willReturnSelf();
        $optionsCollectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($option);
        $this->optionCollectionFactoryMock->method('create')
            ->willReturn($optionsCollectionMock);

        $selections = [
            ['option_id' => 1, 'product_id' => 11],
            ['option_id' => 1, 'product_id' => 12]
        ];
        $bundle = $this->createMock(Bundle::class);
        $bundle->expects($this->once())->method('getSelectionsData')
            ->with($this->linkField)
            ->willReturn($selections);
        $this->bundleFactoryMock->expects($this->once())->method('create')->willReturn($bundle);

        $selection = $this->createPartialMock(Selection::class, ['save', 'getId']);
        $selection->expects($this->once())->method('save');
        $selection->expects($this->once())->method('getId')->willReturn(42);
        $this->bundleSelectionMock->expects($this->once())->method('create')->willReturn($selection);
        $result = $this->model->addChild($productMock, 1, $productLink);
        $this->assertEquals(42, $result);
    }

    /**
     * @return void
     */
    public function testSaveChild(): void
    {
        $id = 12;
        $optionId = 1;
        $position = 3;
        $qty = 2;
        $priceType = 1;
        $price = 10.5;
        $canChangeQuantity = true;
        $isDefault = true;
        $linkProductId = 45;
        $parentProductId = 32;
        $bundleProductSku = 'bundleProductSku';

        $productLink = $this->getMockBuilder(LinkInterface::class)
            ->onlyMethods(['getSku', 'getOptionId'])
            ->addMethods(['getSelectionId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productLink->method('getSku')->willReturn('linked_product_sku');
        $productLink->method('getId')->willReturn($id);
        $productLink->method('getOptionId')->willReturn($optionId);
        $productLink->method('getPosition')->willReturn($position);
        $productLink->method('getQty')->willReturn($qty);
        $productLink->method('getPriceType')->willReturn($priceType);
        $productLink->method('getPrice')->willReturn($price);
        $productLink->method('getCanChangeQuantity')
            ->willReturn($canChangeQuantity);
        $productLink->method('getIsDefault')->willReturn($isDefault);
        $productLink->method('getSelectionId')->willReturn($optionId);

        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn($this->linkField);
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())->method('getTypeId')->willReturn(Type::TYPE_CODE);
        $productMock
            ->method('getData')
            ->with($this->linkField)
            ->willReturn($parentProductId);

        $linkedProductMock = $this->createMock(Product::class);
        $linkedProductMock->method('getId')->willReturn($linkProductId);
        $linkedProductMock->expects($this->once())->method('isComposite')->willReturn(false);
        $this->productRepository
            ->method('get')
            ->withConsecutive([$bundleProductSku], ['linked_product_sku'])
            ->willReturnOnConsecutiveCalls($productMock, $linkedProductMock);

        $store = $this->createMock(Store::class);
        $this->storeManagerMock->method('getStore')->willReturn($store);
        $store->method('getId')->willReturn(0);

        $selection = $this->getMockBuilder(Selection::class)
            ->addMethods(
                [
                    'setProductId',
                    'setParentProductId',
                    'setOptionId',
                    'setPosition',
                    'setSelectionQty',
                    'setSelectionPriceType',
                    'setSelectionPriceValue',
                    'setSelectionCanChangeQty',
                    'setIsDefault'
                ]
            )
            ->onlyMethods(['save', 'getId', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
        $selection->expects($this->once())->method('save');
        $selection->expects($this->once())->method('load')->with($id)->willReturnSelf();
        $selection->method('getId')->willReturn($id);
        $selection->expects($this->once())->method('setProductId')->with($linkProductId);
        $selection->expects($this->once())->method('setParentProductId')->with($parentProductId);
        $selection->expects($this->once())->method('setOptionId')->with($optionId);
        $selection->expects($this->once())->method('setPosition')->with($position);
        $selection->expects($this->once())->method('setSelectionQty')->with($qty);
        $selection->expects($this->once())->method('setSelectionPriceType')->with($priceType);
        $selection->expects($this->once())->method('setSelectionPriceValue')->with($price);
        $selection->expects($this->once())->method('setSelectionCanChangeQty')->with($canChangeQuantity);
        $selection->expects($this->once())->method('setIsDefault')->with($isDefault);

        $this->bundleSelectionMock->expects($this->once())->method('create')->willReturn($selection);
        $this->assertTrue($this->model->saveChild($bundleProductSku, $productLink));
    }

    /**
     * @return void
     */
    public function testRemoveChild(): void
    {
        $this->productRepository->method('get')->willReturn($this->product);
        $bundle = $this->createMock(Bundle::class);
        $this->bundleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($bundle);
        $productSku = 'productSku';
        $optionId = 1;
        $productId = 1;
        $childSku = 'childSku';

        $this->product->method('getTypeId')
            ->willReturn(Type::TYPE_CODE);

        $this->getRemoveOptions();

        $selection = $this->getMockBuilder(Selection::class)
            ->onlyMethods(['__wakeup'])
            ->addMethods(['getSku', 'getOptionId', 'getSelectionId', 'getProductId'])
            ->disableOriginalConstructor()
            ->getMock();
        $selection->method('getSku')->willReturn($childSku);
        $selection->method('getOptionId')->willReturn($optionId);
        $selection->method('getSelectionId')->willReturn(55);
        $selection->method('getProductId')->willReturn($productId);

        $this->option->method('getSelections')->willReturn([$selection]);
        $this->metadataMock->method('getLinkField')->willReturn($this->linkField);
        $this->product->method('getData')
            ->with($this->linkField)
            ->willReturn(3);

        $bundle->expects($this->once())->method('dropAllUnneededSelections')->with(3, []);
        $bundle->expects($this->once())->method('removeProductRelations')->with(3, [$productId]);
        //Params come in lowercase to method
        $this->assertTrue($this->model->removeChild($productSku, $optionId, $childSku));
    }

    /**
     * @return void
     */
    private function getOptions(): void
    {
        $this->product->method('getTypeInstance')
            ->willReturn($this->productType);
        $this->product->expects($this->once())
            ->method('getStoreId')
            ->willReturn($this->storeId);
        $this->productType->expects($this->once())
            ->method('setStoreFilter')
            ->with($this->storeId, $this->product);

        $this->productType->expects($this->once())
            ->method('getOptionsCollection')
            ->with($this->product)
            ->willReturn($this->optionCollection);
    }

    /**
     * @return void
     */
    public function getRemoveOptions(): void
    {
        $this->product->method('getTypeInstance')
            ->willReturn($this->productType);
        $this->product->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);

        $this->productType->expects($this->once())->method('setStoreFilter');
        $this->productType->expects($this->once())->method('getOptionsCollection')
            ->with($this->product)
            ->willReturn($this->optionCollection);

        $this->productType->expects($this->once())
            ->method('getOptionsIds')
            ->with($this->product)
            ->willReturn([1, 2, 3]);

        $this->productType->expects($this->once())
            ->method('getSelectionsCollection')
            ->willReturn([]);

        $this->optionCollection->method('appendSelections')
            ->with([], true)
            ->willReturn([$this->option]);
    }
}
