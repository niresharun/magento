<?php

namespace Ziffity\Shipping\Test\Unit\Ui\Component\ShippingProfile\Form;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use PHPUnit\Framework\TestCase;
use Ziffity\Shipping\Ui\Component\ShippingProfile\Form\DataProvider;
use Ziffity\Shipping\Model\ProfileCharge\ResourceModel\ProfileCharge\CollectionFactory as ProfileChargeCollection;
use Ziffity\Shipping\Model\ShippingProfile\ResourceModel\ShippingProfile\CollectionFactory;
use Ziffity\Shipping\Helper\Data;

class TestDataProvider extends TestCase
{
    /**
     * @dataProvider dataProviderForGetData
     * @covers \Ziffity\Shipping\Ui\Component\ShippingProfile\Form\DataProvider::getData
     * @param array $expected
     * @param string|null $param
     * @return void
     */
    public function testGetData($expected, $param)
    {
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $requestMock->expects($this->any())
            ->method('getParam')
            ->with('profile_id')
            ->willReturn($param);
        $collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection = $this->getMockBuilder(AbstractCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionFactory->expects($this->any())->method('create')->willReturn($collection);
        $profileChargeCollection = $this->getMockBuilder(ProfileChargeCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $profileChargeCollection->expects($this->any())->method('create')->willReturn($collection);
        $helperMock = $this->getMockBuilder(Data::class)
            ->setMethods(['buildShippingProfile','buildDynamicRows'])
            ->disableOriginalConstructor()
            ->getMock();
        if ($param!==null) {
            $helperMock->expects($this->once())
                ->method('buildShippingProfile')
                ->willReturn($expected);
            $helperMock->expects($this->once())
                ->method('buildDynamicRows')
                ->willReturn($expected);
        }
        $instance = new DataProvider(
            "",
            "",
            "",
            $requestMock,
            $collectionFactory,
            $profileChargeCollection,
            $helperMock
        );
        $actual = $instance->getData();
        $this->assertSame($expected, $actual);
    }

    public function dataProviderForGetData()
    {
        return [
            ['expected'=>["1"=>["profile_id"=>"1",
                "profile_name"=>"Custom Oversize Profile Creation",
                "dynamic_rows"=>["0"=>["charge_id"=>"1",
                    "profile_id"=>"1","product_cost_min"=>"1.0000",
                    "product_cost_max"=>"10.0000",
                    "shipping_charge_type"=>"1",
                    "shipping_charge"=>"7.0000",
                    "record_id"=>0]]]],
                'profile_id'=>1],
            ['expected'=>["1"=>["profile_id"=>"1",
                "profile_name"=>"Custom Shipping Profile Creation",
                "dynamic_rows"=>[]]],
                'profile_id'=>1],
            ['expected'=>[],'profile_id'=>null]
        ];
    }
}
