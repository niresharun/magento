<?php
declare(strict_types=1);

namespace Ziffity\ProductCustomizer\Test\Unit\Model\Components\Measurements;

use Ziffity\ProductCustomizer\Helper\Data as Helper;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Ziffity\ProductCustomizer\Model\Components\Measurements\FrameSize;

class FrameSizeTest extends TestCase
{

    /**
     * @var FrameSize
     */
    private $frameSize;

    /**
     * @var Helper|MockObject
     */
    private $helperMock;

    protected function setUp(): void
    {
        $this->helperMock = $this->createMock(Helper::class);

        $this->frameSize = new FrameSize(
            $this->helperMock
        );
    }

    /**
     * @covers \Ziffity\ProductCustomizer\Test\Unit\Model\Components\Measurements\FrameSize::getOverallWidth
     * @dataProvider getSelectionData
     * @return void
     */
    public function testGetOverallWidth($selectionData)
    {
        $innerFrameWidth = $this->frameSize->getInnerFrameWidth($selectionData);
        if (!empty($selectionData['frame']['active_item'])) {
            $layerHeight = $selectionData['frame']['active_item']['height']['integer']
                . ' '
                . $selectionData['frame']['active_item']['height']['tenth'];
            $layerHeight = $this->helperMock->expects($this->atLeastOnce())
                ->method('fractionalToFloat')->with($layerHeight) ->willReturnSelf();

            $result = $innerFrameWidth + $layerHeight * 2;
        } else {
            $result = round((float)$innerFrameWidth, 4);
        }


        $this->assertSame($result, $this->frameSize->getOverallWidth($selectionData));

    }

    /**
     * @covers \Ziffity\ProductCustomizer\Test\Unit\Model\Components\Measurements\FrameSizeTest::getOverallHeight
     * @dataProvider getSelectionData
     * @return void
     */
    public function testGetOverallHeight(array $selectionData)
    {
        $innerFrameHeight = $this->frameSize->getInnerFrameHeight($selectionData);
        if (!empty($selectionData['frame']['active_item'])) {
            $layerHeight = $selectionData['frame']['active_item']['height']['integer'].' '. $selectionData['frame']['active_item']['height']['tenth'];
            $layerHeight = $this->helperMock->expects($this->atLeastOnce())
                ->method('fractionalToFloat')->with($layerHeight) ->willReturnSelf();
            $result = $innerFrameHeight + $layerHeight * 2;
        } else {
        $result = round((float)$innerFrameHeight, 4);
        }
        $this->assertSame($result, $this->frameSize->getOverallHeight($selectionData));
    }

    /**
     * @return array
     */
    public function getSelectionData(): array
    {
        return [
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
            ],
            [
                'size' => [
                    'type' => 'frame', // frame|graphic
                    'width' => [
                        'integer' => 24,
                        'tenth' => 0
                    ],
                    'height' => [
                        'integer' => 36,
                        'tenth' => 0
                    ],
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
                            'tenth' => '3/16'
                        ],
                        'back_of_moulding_width' => '0.9375'
                    ]
                ]
            ],
            [
                'somekey' => []
            ]
        ];
    }
}
