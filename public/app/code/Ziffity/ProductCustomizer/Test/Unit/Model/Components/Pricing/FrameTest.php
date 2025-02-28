<?php
declare(strict_types=1);

namespace Ziffity\ProductCustomizer\Test\Unit\Model\Components\Pricing;

use Magento\Directory\Model\PriceCurrency;
use Ziffity\ProductCustomizer\Model\Components\Measurements\FrameSize;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Ziffity\ProductCustomizer\Helper\Data as Helper;
use Ziffity\ProductCustomizer\Model\Components\Pricing\Frame;

class FrameTest extends TestCase
{
    /**
     * @var Frame
     */
    protected $frame;

    /**
     * @var Helper|MockObject
     */
    protected $helperMock;

    /**
     * @var PriceCurrency|MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var FrameSize|MockObject
     */
    protected $frameSizeMock;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;


    protected function setUp(): void
    {
        $this->helperMock = $this->createMock(Helper::class);
        $this->priceCurrencyMock = $this->createMock(PriceCurrency::class);
        $this->frameSizeMock = $this->createMock(FrameSize::class);
        $this->registryMock = $this->createMock(Registry::class);

        $this->frame = new Frame(
            $this->helperMock,
            $this->priceCurrencyMock,
            $this->frameSizeMock,
            $this->registryMock
        );
    }

    /**
     * @covers \Ziffity\ProductCustomizer\Test\Unit\Model\Components\Pricing\Frame::getPrice
     * @dataProvider productDataProvider
     * @return void
     */
    public function testGetPrice($product, $selectionData)
    {
        $pricePerInch = $product->getData('price');

        $width = $this->frameSizeMock->getOverallWidth($selectionData);
        $height = $this->frameSizeMock->getOverallHeight($selectionData);

        $width = !empty($width) ? $width : $product->getData('layer_width');
        $height = !empty($height) ? $height : $product->getData('layer_height');

        $perimeter = (((float)$width + (float)$height) * 2);

        $initialPrice = $perimeter * $pricePerInch;

        $this->frame->getPrice($product, $selectionData);

    }


    /**
     * @return array
     */
    public function productDataProvider(): array
    {
        return [
            [
                'product' => new DataObject([
                    'price' => 0.1200,
                    'waste_factor' => 0.2000,
                    'labor_factor' => 0.2000,
                    'freight_in_factor' => 0.2000,
                    'overhead_factor' => 0.2000,
                    'packaging_factor' => 0.2000,
                    'profit_percentage' => 84.9200,
                    'relation' => 'laminate_interior',
                ]),
                [
                    'size' => [
                        'type' => 'graphic', // frame|graphic
                        'width' =>
                            [
                                'integer' => 23,
                                'tenth' => 0
                            ],
                        'height' => [
                            'integer' => 35,
                            'tenth' => 0
                        ],
                        'thickness' => 1  // For laminate
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
                                'integer' => '0',
                                'tenth' => '5/8'
                            ],
                            'back_of_moulding_width' => '0.0625'
                        ]
                    ],

                    // below details are for graphic sizeType
                    'mat' => [
                        'overlap' => '1/4', // attribute data
                        'sizes' => [
                            'top' => [
                                'integer' => 2,
                                'tenth' => 0
                            ],
                            'reveal' => 0 // For non top mat
                        ],
                        'active_items' => [
                            'top' => [
                                'id' => 123
                            ]

                        ],
                        'openings' => [
                            'type' => null // Single
                        ]
                    ],
                    'crheader' => [
                        'size' => [
                            'height' => 5,
                            'width' => 5
                        ]
                    ],
                    'label' => [
                        'size' => [
                            'height' => 0,
                            'width' => 0
                        ]
                    ], // size is used

                    //For laminate
                    "laminate" => [
                        "exterior_finish" => [
                            "active_item" => [
                                "id" => 1372
                            ]
                        ],
                        "interior_finish" => [
                            "active_item" => [
                                "id" => 1373
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}
