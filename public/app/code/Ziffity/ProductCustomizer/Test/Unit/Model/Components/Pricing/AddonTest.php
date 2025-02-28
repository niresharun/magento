<?php
declare(strict_types=1);

namespace Ziffity\ProductCustomizer\Test\Unit\Model\Components\Pricing;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Ziffity\ProductCustomizer\Model\Components\Pricing\Addons;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class AddonTest extends TestCase
{
    /**
     * @var Addons
     */
    protected $addon;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        'custom_frame/component_price/addon_plunge_price',
                        ScopeInterface::SCOPE_STORE,
                        8,
                        35,
                    ]
                ]
            );
        $this->addons = new Addons(
            $this->scopeConfigMock
        );
    }

    /**
     * @covers Ziffity\ProductCustomizer\Model\Components\Pricing\Addons::getPrice
     * @return void
     */
    public function testGetPrice()
    {
        $selection = [
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
        ];
        if (isset($selection['form_data'])) {
            foreach ($selection['form_data'] as $formData) {
                if ($formData['name'] !== 'plunge_lock') {
                    continue;
                }
                if ($formData['value'] === 'include') {
                    $resultExpected = 35;
                }
            }
        } else {
            $resultExpected = 0;
        }
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn($resultExpected);

        $this->assertEquals(0, $this->addons->getPrice($selection));
    }
}
