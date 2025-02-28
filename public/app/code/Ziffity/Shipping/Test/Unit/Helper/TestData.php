<?php

namespace Ziffity\Shipping\Test\Unit\Helper;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Exception;
use PHPUnit\Framework\TestCase;
use Ziffity\Shipping\Helper\Data;
use Ziffity\Shipping\Model\ProfileCharge\ProfileCharge;
use Ziffity\Shipping\Model\ProfileCharge\ProfileChargeFactory;
use Ziffity\Shipping\Model\OversizeProfileCharge\ResourceModel\ProfileCharge as ResourceModel;
use Ziffity\Shipping\Model\ProfileCharge\ResourceModel\ProfileCharge\CollectionFactory;

class TestData extends TestCase
{
    /**
     * @dataProvider dataProviderPrepareNewDataToCompare
     * @covers \Ziffity\Shipping\Helper\Data::prepareNewDataToCompare
     * @param array $expected
     * @param string $profileId
     * @param array $param
     * @return void
     */
    public function testPrepareNewDataToCompare($expected, $profileId, $param)
    {
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $requestMock->expects($this->once())
            ->method('getParam')
            ->with('dynamic_rows')
            ->willReturn($param);
        $actual = $this->createMockHelperObject();
        $actual = $actual->prepareNewDataToCompare($requestMock, $profileId);
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider dataProviderPrepareOldDataToCompare
     * @covers \Ziffity\Shipping\Helper\Data::prepareOldDataToCompare
     * @param array $expected
     * @param string|null $profileId
     * @return void
     */
    public function testPrepareOldDataToCompare($expected, $profileId)
    {
        $itemsArray = [];
        foreach ($this->actualDynamicRowsOldData() as $value) {
            $dataObject = $this->createNewDataObject();
            $itemsArray[] = $dataObject->setData($value);
        }
        $collection = $this->getMockBuilder(AbstractCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->any())->method('addFieldToFilter')->willReturn($collection);
        if ($profileId == null) {
            $collection->expects($this->any())->method('getItems')->willReturn(new \ArrayIterator([]));
        }
        if ($profileId!==null) {
            $collection->expects($this->any())->method('getItems')->willReturn(new \ArrayIterator($itemsArray));
        }
        $collectionFactory = $this->getMockBuilder(
            AbstractCollection::class
        )->setMethods(['create'])->disableOriginalConstructor()
            ->getMock();
        $collectionFactory->expects($this->any())->method('create')->willReturn($collection);
        $actual = $this->createMockHelperObject();
        $actual = $actual->prepareOldDataToCompare($collectionFactory, $profileId);
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider dataProviderForPrepareNewData
     * @covers Data::prepareNewData()
     * @param array $param
     * @param string $profileId
     * @return void
     */
    public function testPrepareNewData($expected, $param, $profileId)
    {
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $requestMock->expects($this->once())
            ->method('getParam')
            ->with('dynamic_rows')
            ->willReturn($param);
        $actual = $this->createMockHelperObject();
        $actual = $actual->prepareNewData($requestMock, $profileId);
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider dataProviderForModelResourceModel
     * @covers Data::insertValues()
     * @param string $modelObj
     * @param string $resourceModel
     * @return void
     */
    public function testInsertValues($modelObj, $resourceModel)
    {
        try {
            $helperObject = new \ReflectionClass(Data::class);
            $insertValuesMethod = $helperObject->getMethod('insertValues');
            $insertValuesMethod->setAccessible(true);
            $helperInstance = $this->createMockHelperObject();
            $data = $this->actualDynamicRowsData();
            $resourceModel = $this->createMock($resourceModel);
            $modelInstance = $this->createMock(ProfileCharge::class);
            $model = $this->createMock($modelObj);
            $model->expects($this->any())->method('create')
                ->willReturn($modelInstance);
            $resourceModel->expects($this->any())
                ->method('load')
                ->willReturn($modelInstance);
            $resourceModel->expects($this->any())
                ->method('save')
                ->willReturn($model);
            $insertValuesMethod->invoke($helperInstance, $model, $resourceModel, $data);
        } catch (Exception $exception) {
            $this->fail();
        }
        $this->assertTrue(true);
    }

    /**
     * @covers Data::deleteValues()
     * @return void
     */
    public function testDeleteValues()
    {
        try {
            $modelObject = $this->createMock(ProfileCharge::class);
            $modelFactoryInstance = $this->createMock(ProfileChargeFactory::class);
            $modelFactoryInstance->expects($this->any())->method('create')
                ->willReturn($modelObject);
            $resourceModel = $this->createMock(ResourceModel::class);
            $resourceModel->expects($this->any())
                ->method('load')
                ->willReturn($modelObject);
            $resourceModel->expects($this->any())
                ->method('delete')
                ->willReturn($modelObject);
            $helperInstance = $this->createMockHelperObject();
            $helperInstance->deleteValues($modelFactoryInstance, $resourceModel, $this->actualDynamicRowsData());
        } catch (Exception $exception) {
            $this->assertTrue(false);
        }
        $this->assertTrue(true);
    }

    /**
     * @dataProvider dataProviderForProfileData
     * @covers Data::buildShippingProfile()
     * @param array $expected
     * @param array $data
     * @param string|null $profileId
     * @return void
     */
    public function testBuildShippingProfile($expected, $data, $profileId)
    {
        $collection = $this->getMockBuilder(AbstractCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->any())->method('addFieldToFilter')->willReturn($collection);
        if ($profileId == null) {
            $collection->expects($this->any())->method('getItems')->willReturn(new \ArrayIterator([]));
        }
        if ($profileId!==null) {
            $collection->expects($this->any())->method('getItems')->willReturn(new \ArrayIterator($data));
        }
        $helperInstance = $this->createMockHelperObject();
        $actual = $helperInstance->buildShippingProfile($profileId, $collection, []);
        $this->assertSame($expected, $actual);
    }

    public function dataProviderForProfileData()
    {
        return[
            ['expected'=>['1'=>['profile_id'=>"1",
                "profile_name"=>"custom shipping"]],
                'data'=>[$this->createNewDataObject()
                    ->setData(['profile_id'=>"1",
                    "profile_name"=>"custom shipping"])],
                "profile_id"=>"1"],
            ['expected'=>[],'data'=>[],"profile_id"=>null]
        ];
    }

    /**
     * @return DataObject
     */
    public function createNewDataObject()
    {
        return new DataObject();
    }

    /**
     * @covers Data::updateValues()
     * @return void
     */
    public function testUpdateValues()
    {
        try {
            $modelObject = $this->createMock(ProfileCharge::class);
            $modelFactoryInstance = $this->createMock(ProfileChargeFactory::class);
            $modelFactoryInstance->expects($this->any())->method('create')
                ->willReturn($modelObject);
            $resourceModel = $this->createMock(ResourceModel::class);
            $resourceModel->expects($this->any())
                ->method('load')
                ->willReturn($modelObject);
            $resourceModel->expects($this->any())
                ->method('delete')
                ->willReturn($modelObject);
            $helperInstance = $this->createMockHelperObject();
            $helperInstance->deleteValues($modelFactoryInstance, $resourceModel, $this->actualDynamicRowsData());
        } catch (Exception $exception) {
            $this->assertTrue(false);
        }
        $this->assertTrue(true);
    }

    /**
     * @dataProvider dataProviderForProfileData()
     * @covers Data::buildDynamicRows()
     * @param array $expected
     * @param array $data
     * @param string|null $profileId
     * @return void
     */
    public function testBuildDynamicRows($expected, $data, $profileId)
    {
        $collectionFactory = $this->getMockBuilder(CollectionFactory::class)
        ->disableOriginalConstructor()
        ->getMock();
        $collection = $this->getMockBuilder(AbstractCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionFactory->expects($this->any())->method('create')->willReturn($collection);
        $collection->expects($this->any())->method('addFieldToFilter')->willReturn($collection);
        if ($profileId == null) {
            $collection->expects($this->any())->method('getItems')->willReturn(new \ArrayIterator([]));
            $reworkedExpected[$profileId]['dynamic_rows'] = $data;
        }
        if ($profileId!==null) {
            $collection->expects($this->any())->method('getItems')->willReturn(new \ArrayIterator($data));
            $reworkedExpected = [];
            $result = [];
            $count = 0;
            foreach ($expected as $value) {
                $result[$count] = $value;
                $result[$count]['record_id'] = $count;
                $count++;
            }
            $reworkedExpected[$profileId]['dynamic_rows'] = $result;
        }
        $helperInstance = $this->createMockHelperObject();
        $actual = $helperInstance->buildDynamicRows($profileId, $collectionFactory, []);
        if (isset($reworkedExpected) && !empty($reworkedExpected)) {
            $expected = $reworkedExpected;
        }
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider dataProviderForOldNewData
     * @covers Data::compareUpdateData()
     * @param array $expected
     * @param array $newData
     * @param array $oldData
     * @return void
     */
    public function testCompareUpdateData($expected, $newData, $oldData)
    {
        $helperInstance = $this->createMockHelperObject();
        $actual = $helperInstance->compareUpdateData($newData, $oldData);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return Data|Object
     */
    public function createMockHelperObject()
    {
        $mockDate = new \Magento\Framework\Stdlib\DateTime;
        $mockAttributeInterface = $this->createMock(\Magento\Eav\Api\AttributeRepositoryInterface::class);
        $mockResourceConnection = $this->createMock(ResourceConnection::class);
        return (new ObjectManagerHelper($this))->getObject(
            Data::class,
            [
                'MockDate' => $mockDate,
                'AttributeRepositoryInterface'=> $mockAttributeInterface,
                'resourceConnection'=> $mockResourceConnection
            ]
        );
    }

    /**
     * @covers Data::saveLogic()
     * @return void
     */
    public function testSaveLogic()
    {
        $dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->setMethods(
                ['formatDate']
            )->disableOriginalConstructor(
            )->getMock();
        $mockObj = $this->getMockBuilder(Data::class)
            ->setMethods(['saveShippingCharges'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockObj->dateTime = $dateTimeMock;
        $mockObj->expects($this->once())
            ->method('saveShippingCharges')
            ->willReturnSelf();
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(['profile_id'=>"1",'profile_name'=>"custom test shipping"]);
        $modelObject = $this->createMock(ProfileCharge::class);
        $modelFactoryInstance = $this->createMock(ProfileChargeFactory::class);
        $modelFactoryInstance->expects($this->any())->method('create')
            ->willReturn($modelObject);
        $resourceModel = $this->createMock(ResourceModel::class);
        $resourceModel->expects($this->any())
            ->method('load')
            ->willReturn($modelObject);
        $resourceModel->expects($this->any())
            ->method('save')
            ->willReturn($modelObject);
        $actual = $mockObj
            ->saveLogic(
                $modelFactoryInstance,
                $resourceModel,
                $requestMock,
                'profile',
                'model',
                'resourceModel'
            );
        $this->assertSame(null, $actual->getData());
    }

    public function dataProviderForOldNewData()
    {
        $expected = '{"3":{"charge_id":"3","profile_id":"2",
        "product_cost_min":"10.0000",
        "product_cost_max":"22.0000",
        "shipping_charge_type":"1",
        "shipping_charge":"20.0000"},
        "4":{"charge_id":"4","profile_id":"2",
        "product_cost_min":"20.0000",
        "product_cost_max":"30.0000",
        "shipping_charge_type":"1",
        "shipping_charge":"27.0000"}}';
        $expected = json_decode($expected, true);
        $oldData = '{"2":{"charge_id":"2","profile_id":"2",
        "product_cost_min":"1.0000",
        "product_cost_max":"10.0000",
        "shipping_charge_type":"1",
        "shipping_charge":"15.0000"},
        "3":{"charge_id":"3","profile_id":"2",
        "product_cost_min":"10.0000",
        "product_cost_max":"21.0000",
        "shipping_charge_type":"1",
        "shipping_charge":"20.0000"},
        "4":{"charge_id":"4","profile_id":"2",
        "product_cost_min":"20.0000",
        "product_cost_max":"30.0000",
        "shipping_charge_type":"1",
        "shipping_charge":"26.0000"}}';
        $oldData = json_decode($oldData, true);
        $newData = '{"2":{"charge_id":"2","profile_id":"2",
        "product_cost_min":"1.0000",
        "product_cost_max":"10.0000",
        "shipping_charge_type":"1",
        "shipping_charge":"15.0000"},
        "3":{"charge_id":"3","profile_id":"2",
        "product_cost_min":"10.0000",
        "product_cost_max":"22.0000",
        "shipping_charge_type":"1",
        "shipping_charge":"20.0000"},
        "4":{"charge_id":"4","profile_id":"2",
        "product_cost_min":"20.0000",
        "product_cost_max":"30.0000",
        "shipping_charge_type":"1",
        "shipping_charge":"27.0000"}}';
        $newData = json_decode($newData, true);
        $extraSet = $this->buildNewOldData();
        return [
            [
                'expected'=>$expected,
                'new_data'=>$newData,
                'old_data'=>$oldData
            ],
            [
                'expected'=>[],
                'new_data'=>$extraSet['new_data'],
                'old_data'=>$extraSet['old_data']
            ]
        ];
    }

    public function buildNewOldData()
    {
        $oldData = '{"2":{"charge_id":"2","profile_id":"2",
        "product_cost_min":"1.0000",
        "product_cost_max":"10.0000",
        "shipping_charge_type":"1",
        "shipping_charge":"15.0000"},
        "3":{"charge_id":"3","profile_id":"2",
        "product_cost_min":"10.0000",
        "product_cost_max":"21.0000",
        "shipping_charge_type":"1",
        "shipping_charge":"20.0000"},
        "4":{"charge_id":"4","profile_id":"2",
        "product_cost_min":"20.0000",
        "product_cost_max":"30.0000",
        "shipping_charge_type":"1",
        "shipping_charge":"26.0000"}}';
        $oldData = json_decode($oldData, true);
        $newData = '{"2":{"charge_id":"2","profile_id":"2",
        "product_cost_min":"1.0000",
        "product_cost_max":"10.0000",
        "shipping_charge_type":"1",
        "shipping_charge":"15.0000"},
        "3":{"charge_id":"3","profile_id":"2",
        "product_cost_min":"10.0000",
        "product_cost_max":"21.0000",
        "shipping_charge_type":"1",
        "shipping_charge":"20.0000"},
        "4":{"charge_id":"4","profile_id":"2",
        "product_cost_min":"20.0000",
        "product_cost_max":"30.0000",
        "shipping_charge_type":"1",
        "shipping_charge":"26.0000"}}';
        $newData = json_decode($newData, true);
        return ['new_data'=>$newData,'old_data'=>$oldData];
    }

    public function dataProviderForModelResourceModel()
    {
        return [
            ['modelObj'=>ProfileChargeFactory::class,
                'resourceModel'=>ResourceModel::class]
        ];
    }

    public function dataProviderForPrepareNewData()
    {
        $actualDynamicRowsData = $this->actualDynamicRowsData();
        $reworkedData = [];
        foreach ($actualDynamicRowsData as $key => $value) {
            $value['charge_id'] = null;
            $value['profile_id'] = "1";
            if (isset($value['record_id'])) {
                unset($value['record_id']);
            }
            $reworkedData[$key] = $value;
        }
        return[
            ['expected'=>$reworkedData,
                'param'=>$actualDynamicRowsData,
                'profile_id'=>"1"]
        ];
    }

    public function dataProviderPrepareOldDataToCompare()
    {
        $actualDynamicRowsData = $this->actualDynamicRowsOldData();
        return [
            ['expected'=>[],'profile_id'=>null],
            ['expected'=>
                $this->expectedDynamicRowsOldData($actualDynamicRowsData),
                'profile_id'=>1]
        ];
    }

    public function dataProviderPrepareNewDataToCompare()
    {
        $actualDynamicRowsData = $this->actualDynamicRowsData();
        return [
            ['expected'=>[],'profile_id'=>1,'param'=>[]],
            ['expected'=>
                $this->expectedDynamicRowsData($actualDynamicRowsData, 1),
            'profile_id'=>1,
                'param'=>$actualDynamicRowsData]
        ];
    }

    public function expectedDynamicRowsOldData($data)
    {
        $resultData = [];
        foreach ($data as $value) {
            $resultData[$value['charge_id']] = $value;
        }
        return $resultData;
    }

    public function expectedDynamicRowsData($data, $profileId)
    {
        $resultData = [];
        foreach ($data as $value) {
            if (isset($value['record_id'])) {
                unset($value['record_id']);
            }
            $value['profile_id'] = $profileId;
            if ($value['charge_id'] == "null") {
                $value['charge_id'] = implode($value);
            }
            $resultData[$value['charge_id']] = $value;
        }
        return $resultData;
    }

    public function actualDynamicRowsData()
    {
        return [
            [
                "charge_id" => "2",
                "profile_id" => "2",
                "product_cost_min" => "1",
                "product_cost_max" => "10",
                "shipping_charge_type" => "1",
                "shipping_charge" => "15",
                "record_id" => "0"
            ],
            [
                "record_id" => "2",
                "charge_id" => "null",
                "product_cost_min" => "20",
                "product_cost_max" => "30",
                "shipping_charge_type" => "1",
                "shipping_charge" => "25"
            ],
            [
                "charge_id" => "3",
                "profile_id" => "2",
                "united_inch_min" => "1",
                "united_inch_max" => "10",
                "shipping_charge_type" => "1",
                "shipping_charge" => "15",
                "record_id" => "0"
            ],
            [
                "record_id" => "2",
                "charge_id" => "null",
                "united_inch_min" => "20",
                "united_inch_max" => "30",
                "shipping_charge_type" => "1",
                "shipping_charge" => "25"
            ]
        ];
    }

    public function actualDynamicRowsOldData()
    {
        return [
            [
                "charge_id" => "2",
                "profile_id" => "2",
                "product_cost_min" => "1",
                "product_cost_max" => "10",
                "shipping_charge_type" => "1",
                "shipping_charge" => "15",
                "record_id" => "0"
            ],
            [
                "record_id" => "2",
                "charge_id" => "4",
                "product_cost_min" => "20",
                "product_cost_max" => "30",
                "shipping_charge_type" => "1",
                "shipping_charge" => "25"
            ],
            [
                "charge_id" => "3",
                "profile_id" => "2",
                "united_inch_min" => "1",
                "united_inch_max" => "10",
                "shipping_charge_type" => "1",
                "shipping_charge" => "15",
                "record_id" => "0"
            ],
            [
                "record_id" => "2",
                "charge_id" => "5",
                "united_inch_min" => "20",
                "united_inch_max" => "30",
                "shipping_charge_type" => "1",
                "shipping_charge" => "25"
            ]
        ];
    }

    /**
     * Unit Test function for isAllowedToDelete
     *
     * @dataProvider dataProviderForIsAllowedToDelete
     * @covers \Ziffity\Shipping\Helper\Data::isAllowedToDelete
     * @param array $expected
     * @param string $attributeCode
     * @param DataObject $model
     * @param array $records
     * @return void
     */
    public function testIsAllowedToDelete($expected, $attributeCode, $model, $records)
    {
        $mockDataObject = $this->createNewDataObject();
        $mockDate = new \Magento\Framework\Stdlib\DateTime;
        $mockAttributeInterface = $this->getMockForAbstractClass(\Magento\Eav\Api\AttributeRepositoryInterface::class);
        $mockAttributeInterface->expects($this->any())
            ->method('get')
            ->with('catalog_product', $attributeCode)
            ->willReturn($mockDataObject->setAttributeId(1));
        $mockResourceConnection = $this->createMock(ResourceConnection::class);
        $mockResourceConnection->expects($this->once())
            ->method('getTableName')
            ->with('catalog_product_entity_int')
            ->willReturn('catalog_product_entity_int');
        $connection = $this->getMockForAbstractClass(AdapterInterface::class);
        $dbSelect = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dbSelect->expects($this->any())->method('from')->willReturnSelf();
        $dbSelect->expects($this->any())->method('where')->willReturnSelf();
        $connection->expects($this->once())
            ->method('select')
            ->willReturn($dbSelect);
        $connection->expects($this->once())
            ->method('fetchAll')
            ->willReturn($records);
        $mockResourceConnection->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);
        $messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        if (!empty($expected)) {
            $messageManagerMock->expects($this->any())
                ->method('addErrorMessage')
                ->with(__('This profile "%1" is associated with product id(s)
                            [%2] , please unassign before deleting', $model->getProfileName(), implode(",", $expected)));
        }
        $helperInstance = (new ObjectManagerHelper($this))->getObject(
            Data::class,
            [
                'MockDate' => $mockDate,
                'AttributeRepositoryInterface'=> $mockAttributeInterface,
                'resourceConnection'=> $mockResourceConnection
            ]
        );
        $helperInstance->attributeRepository = $mockAttributeInterface;
        $actual = $helperInstance->isAllowedToDelete($attributeCode, $model, $messageManagerMock);
        $this->assertSame($expected, $actual);
    }

    /**
     * DataProvider for testIsAllowedToDelete function.
     *
     * @return array[]
     */
    public function dataProviderForIsAllowedToDelete()
    {
        return [
            [   'expected'=>[],
                'attribute_code'=>'shipping_profile',
                'model'=>$this->createNewDataObject()
                    ->setData(['profile_id'=>"1",
                        'profile_name'=>'Custom Shipping Profile']),
                'records'=>[]],
            [   'expected'=>["1"],
                'attribute_code'=>'oversize_profile',
                'model'=>$this->createNewDataObject()
            ->setData(['profile_id'=>"2",
                'profile_name'=>"Custom Oversize Profile"]),
                'records'=>json_decode('[{"entity_id":"1"}]', true)]
        ];
    }
}
