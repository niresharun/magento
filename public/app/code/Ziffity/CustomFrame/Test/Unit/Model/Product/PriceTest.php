<?php
namespace Ziffity\CustomFrame\Test\Unit\Model\Product;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Ziffity\ProductCustomizer\Model\Components\Pricing;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ziffity\CustomFrame\Model\Product\Price;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
{
    /**
     * @var Price
     */
    protected $model;

    /**
     * @var Pricing\Frame|MockObject
     */
    protected $frameMock;

    /**
     * @var Pricing\Backingboard|MockObject
     */
    protected $backingboardMock;

    /**
     * @var Pricing\Chalkboards|MockObject
     */
    protected $chalkboardsMock;

    /**
     * @var Pricing\Corkboards|MockObject
     */
    protected $corkboardsMock;

    /**
     * @var Pricing\Dryeraseboard|MockObject
     */
    protected $dryeraseboardMock;

    /**
     * @var Pricing\Fabric|MockObject
     */
    protected $fabricMock;

    /**
     * @var Pricing\Glass|MockObject
     */
    protected $glassMock;

    /**
     * @var Pricing\Laminate|MockObject
     */
    protected $laminateMock;

    /**
     * @var Pricing\Letterboard|MockObject
     */
    protected $letterboardMock;

    /**
     * @var Pricing\Mat|MockObject
     */
    protected $matMock;

    /**
     * @var Pricing\Postfinish|MockObject
     */
    protected $postfinishMock;

    /**
     * @var Pricing\Shelves|MockObject
     */
    protected $shelvesMock;

    /**
     * @var Pricing\Addons|MockObject
     */
    protected $addonsMock;

    /**
     * @var Pricing\Header|MockObject
     */
    protected $headerMock;

    /**
     * @var Pricing\Lables|MockObject
     */
    protected $lablesMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    protected $productRepositoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $className = Price::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var Context $context */
        $ruleFactory = $arguments['ruleFactory'];
        $storeManager  = $arguments['storeManager'];
        $localeDate  = $arguments['localeDate'];
        $customerSession  = $arguments['customerSession'];
        $eventManager  = $arguments['eventManager'];
        $groupManagement  = $arguments['groupManagement'];
        $tierPriceFactory  = $arguments['tierPriceFactory'];
        $config  = $arguments['config'];
        $catalogData  = $arguments['catalogData'];
        $serializer  = $arguments['serializer'];
        $tierPriceExtensionFactory  = $arguments['tierPriceExtensionFactory'];
        $this->priceCurrency = $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        $this->frameMock = $this->createMock(Pricing\Frame::class);
        $this->backingboardMock = $this->createMock(Pricing\Backingboard::class);
        $this->chalkboardsMock = $this->createMock(Pricing\Chalkboards::class);
        $this->corkboardsMock = $this->createMock(Pricing\Corkboards::class);
        $this->dryeraseboardMock = $this->createMock(Pricing\Dryeraseboard::class);
        $this->fabricMock = $this->createMock(Pricing\Fabric::class);
        $this->glassMock = $this->createMock(Pricing\Glass::class);
        $this->laminateMock = $this->createMock(Pricing\Laminate::class);
        $this->letterboardMock = $this->createMock(Pricing\Letterboard::class);
        $this->matMock = $this->createMock(Pricing\Mat::class);
        $this->postfinishMock = $this->createMock(Pricing\Postfinish::class);
        $this->shelvesMock = $this->createMock(Pricing\Shelves::class);
        $this->addonsMock = $this->createMock(Pricing\Addons::class);
        $this->headerMock = $this->createMock(Pricing\Header::class);
        $this->lablesMock = $this->createMock(Pricing\Lables::class);
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);

        $this->model = new Price(
            $ruleFactory,
            $storeManager,
            $localeDate,
            $customerSession,
            $eventManager,
            $this->priceCurrency,
            $groupManagement,
            $tierPriceFactory,
            $config,
            $catalogData,
            $serializer,
            $tierPriceExtensionFactory,
            $this->frameMock,
            $this->backingboardMock,
            $this->chalkboardsMock,
            $this->corkboardsMock,
            $this->dryeraseboardMock,
            $this->fabricMock,
            $this->glassMock,
            $this->laminateMock,
            $this->letterboardMock,
            $this->matMock,
            $this->postfinishMock,
            $this->shelvesMock,
            $this->addonsMock,
            $this->headerMock,
            $this->lablesMock,
            $this->productRepositoryMock
        );
    }


      /**
     * @covers \Ziffity\CustomFrame\Model\Product\Price::getPrice
     * @return void
     */
    public function testGetPrice()
    {
        $selectionData = [
            'size' => [
                'type' => 'graphic', // frame|graphic
                'width' => [
                    'integer' => 12,
                    'tenth' => 0
                ],
                'height' => [
                    'integer' => 14,
                    'tenth' => 0
                ],
                'thickness' => 1  // For laminate & shelves
            ],
            'frame' => [
                'active_item' => [
                    'id' => "123",
                    // attribute data
                    'width' => [
                        'integer' => '110',
                        'tenth' => '0'
                    ],
                    'height' => [
                        'integer' => '1',
                        'tenth' => '3/8'
                    ],
                    'back_of_moulding_width' => '1.1250'
                ]
            ],
            'mat' => [
                'overlap' => '1/4', // attribute data
                'sizes' => [
                    'top' => [
                        'integer' => 1,
                        'tenth' => '1/2'
                    ],
                ],
                'active_items' => [
                    'top' => [
                        'id' => 123,
                        'width' => [
                            'integer' => '10',
                            'tenth' => '0'
                        ],
                        'height' => [
                            'integer' => '10',
                            'tenth' => '0'
                        ],
                    ]
                ],
                'openings' => [
                    'type' => null // Single
                ]
            ],
            'addons' => [
                'form_data' => [
                    '0' => [
                        'name' => 'plunge_lock',
                        'value' => 'include'
                    ],
                    '1' => [
                        'name' => 'hinge_position',
                        'value' => 'left'
                    ]
                ]
            ],
            'shelves' => [
                'form_data' => [
                    '0' => [
                        'name' => 'shelves_qty',
                        'value' => 2
                    ],
                    '1' => [
                        'name' => 'shelves_thickness',
                        'value' => 0.375 // 0.25|0.375
                    ]
                ]
            ],
            'crheader' => [
                'texts' => [
                    '0' => [
                        'width_inch' => '7 11/16',
                        'height_inch' => '2 5/8'
                    ]
                ],
                'images' => [
                    '0' => [
                        'width_inch' => '3 7/8',
                        'height_inch' => '4'
                    ]
                ]
            ],
            'labels' => [
                'texts' => [
                    '0' => [
                        'width_inch' => '5',
                        'height_inch' => '1 3/8'
                    ],
                    '1' => [
                        'width_inch' => '1',
                        'height_inch' => '7/8'
                    ],
                    '2' => [
                        'width_inch' => '3',
                        'height_inch' => '1 1/2'
                    ]
                ],
                'images' => [
                    '0' => [
                        'width_inch' => '1 15/16',
                        'height_inch' => '2'
                    ]
                ]
            ]

        ];
        $product = new DataObject([
            'id' => 1,
            'price' => 0.1200,
            'waste_factor' => 0.2000,
            'labor_factor' => 0.2000,
            'freight_in_factor' => 0.2000,
            'overhead_factor' => 0.2000,
            'packaging_factor' => 0.2000,
            'profit_percentage' => 84.9200,
            'relation' => 'laminate_interior',
        ]);

        $this->priceCurrency->expects($this->any())
            ->method('round')
            ->with(0)
            ->willReturn(0);

        $this->assertEquals(0, $this->model->getPrice($product, $selectionData));
    }
}
