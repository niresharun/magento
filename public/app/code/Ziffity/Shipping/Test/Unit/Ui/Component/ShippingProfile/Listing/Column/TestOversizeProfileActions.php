<?php

namespace Ziffity\Shipping\Test\Unit\Ui\Component\ShippingProfile\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use PHPUnit\Framework\TestCase;
use Ziffity\Shipping\Ui\Component\ShippingProfile\Listing\Column\ShippingProfileActions;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;

class TestOversizeProfileActions extends TestCase
{
    /**
     * @dataProvider dataProviderForPrepareDataSource
     * @covers \Ziffity\Shipping\Ui\Component\OversizeProfile\Listing\Column\OversizeProfileActions::prepareDataSource
     * @param array $expected
     * @param array $data
     * @return void
     */
    public function testPrepareDataSource($expected, $data)
    {
        $contextMock = $this->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor(
            )->getMock();
        $uiComponentFactoryMock = $this->getMockBuilder(UiComponentFactory::class)
            ->disableOriginalConstructor(
            )->getMock();
        $urlMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        if (!empty($data)) {
            $urlMock->expects($this->any())
                ->method('getUrl')
                ->willReturn('some url');
        }
        $listingInstance = new ShippingProfileActions($contextMock, $uiComponentFactoryMock, $urlMock);
        $listingInstance->setData('name', 'actions');
        $actual = $listingInstance->prepareDataSource($data);
        $this->assertSame(json_encode($expected, true), json_encode($actual, true));
    }

    public function dataProviderForPrepareDataSource()
    {
        return [
            ['expected'=>[],'data'=>[]],
            ['expected'=>$this->expectedData(),'data'=>$this->decodedDataSource()]
        ];
    }

    public function decodedDataSource()
    {
        return json_decode('{"data":{"items":[{"id_field_name":"profile_id",
        "profile_id":"6","profile_name":"Custom Shipping profile creation",
        "created_at":"2023-02-27 18:44:57",
        "modified_at":"2023-02-27 18:44:57",
        "orig_data":null}],"totalRecords":1}}', true);
    }

    public function expectedData()
    {
        $result = json_decode('{"data":{"items":[{"id_field_name":"profile_id",
        "profile_id":"6","profile_name":"Custom Shipping profile creation",
        "created_at":"2023-02-27 18:44:57",
        "modified_at":"2023-02-27 18:44:57",
        "orig_data":null,
        "actions":{"edit":{"href":"some url",
        "label":"Edit","hidden":false}}}],"totalRecords":1}}', true);
        return $result;
    }
}
