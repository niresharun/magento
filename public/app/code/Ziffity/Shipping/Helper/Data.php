<?php

namespace Ziffity\Shipping\Helper;

use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime;
//TODO: Save logic in this directory should be moved to model files for later.
class Data
{

    /**
     * @var ResourceConnection
     */
    public $resourceConnection;

    /**
     * @var AttributeRepositoryInterface
     */
    public $attributeRepository;

    /**
     * @var DateTime
     */
    public $dateTime;

    /**
     * @param DateTime $dateTime
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        DateTime $dateTime,
        AttributeRepositoryInterface $attributeRepository,
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->attributeRepository = $attributeRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * This function checks if the attribute is associated with any product or not.
     *
     * @param string $attributeCode
     * @param Object $profile
     * @return array
     * @throws NoSuchEntityException
     */
    public function isAllowedToDelete($attributeCode, $profile)
    {
        $ids = [];
        $attribute = $this->attributeRepository
            ->get('catalog_product', $attributeCode);
        $tableName = $this->resourceConnection->getTableName('catalog_product_entity_int');
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['c'=>$tableName], ['entity_id'])
            ->where('c.value = '.$profile->getProfileId())
            ->where('c.attribute_id = '.$attribute->getAttributeId());
        $records = $connection->fetchAll($select);
        foreach ($records as $record) {
            $ids[] = $record['entity_id'];
        }
        return $ids;
    }

    /**
     * This function prepares the new data to be compared.
     *
     * @param RequestInterface $request
     * @param string|null $profileId
     * @return array
     */
    public function prepareNewDataToCompare($request, $profileId)
    {
        $newData = [];
        $dynamicRows = $request->getParam('dynamic_rows');
        if ($dynamicRows) {
            foreach ($dynamicRows as $value) {
                if (isset($value['record_id'])) {
                    unset($value['record_id']);
                }
                if (isset($value['initialize'])) {
                    unset($value['initialize']);
                }
                $value['profile_id'] = $profileId;
                if ($value['charge_id'] == "null") {
                    $value['charge_id'] = implode($value);
                }
                $newData[$value['charge_id']] = $value;
            }
        }
        return $newData;
    }

    /**
     * This function prepares the old data to be compared.
     *
     * @param Mixed $collection
     * @param string|null $profileId
     * @return array
     */
    public function prepareOldDataToCompare($collection, $profileId)
    {
        $oldData = [];
        $collection = $collection->create();
        $collection->addFieldToFilter('profile_id', $profileId);
        foreach ($collection->getItems() as $value) {
            $oldData[$value->getChargeId()] = $value->getData();
        }
        return $oldData;
    }

    /**
     * This function compares the two arrays values.
     *
     * @param array $newData
     * @param array $oldData
     * @return array
     */
    public function compareUpdateData($newData, $oldData)
    {
        $found = [];
        foreach ($newData as $item => $newDatum) {
            foreach ($oldData as $oldDatum) {
                $result = ($oldDatum['charge_id'] == $newDatum['charge_id']);
                if ($result && !empty(array_diff_assoc($newDatum, $oldDatum))) {
                    $found[$item] = $newDatum;
                }
            }
        }
        return $found;
    }

    /**
     * This function processes and prepares the new data.
     *
     * @param RequestInterface $request
     * @param string|null $profileId
     * @return array
     */
    public function prepareNewData($request, $profileId)
    {
        $newData = [];
        $dynamicRows = $request->getParam('dynamic_rows');
        if($dynamicRows) {
            foreach ($dynamicRows as $value) {
                if (isset($value['record_id'])) {
                    unset($value['record_id']);
                }
                if (isset($value['initialize'])) {
                    unset($value['initialize']);
                }
                $value['profile_id'] = $profileId;
                $value['charge_id'] = null;
                $newData[] = $value;
            }
        }
        return $newData;
    }

    /**
     * This function saves the new values in table.
     *
     * @param Object|Mixed $secondaryModel
     * @param Object|Mixed $chargeResourceModel
     * @param array $data
     * @return void
     */
    protected function insertValues(
        $secondaryModel,
        $chargeResourceModel,
        $data
    ) {
        foreach ($data as $datum) {
            $modelFactory = $secondaryModel->create();
            $chargeResourceModel
                ->load($modelFactory, null, 'charge_id');
            unset($datum['charge_id']);
            $modelFactory->setData($datum);
            $chargeResourceModel->save($modelFactory);
        }
    }

    /**
     * This function deletes the values from the table.
     *
     * @param Object|Mixed $secondaryModel
     * @param Object|Mixed $chargeResourceModel
     * @param array $data
     * @return void
     */
    public function deleteValues(
        $secondaryModel,
        $chargeResourceModel,
        $data
    ) {
        foreach ($data as $datum) {
            $model = $secondaryModel->create();
            $chargeResourceModel
                ->load($model, $datum['charge_id'], 'charge_id');
            $chargeResourceModel->delete($model);
        }
    }

    /**
     * This function updates the values in the table.
     *
     * @param Object|Mixed $secondaryModel
     * @param Object|Mixed $chargeResourceModel
     * @param array $data
     * @param string|null $profileId
     * @return void
     */
    protected function updateValues(
        $secondaryModel,
        $chargeResourceModel,
        $data,
        $profileId
    ) {
        foreach ($data as $datum) {
            $modelFactory = $secondaryModel->create();
            $chargeResourceModel
                ->load($modelFactory, $datum['charge_id'], 'charge_id');
            $modelFactory->setData($datum);
            $modelFactory->setProfileId($profileId);
            $chargeResourceModel->save($modelFactory);
        }
    }

    /**
     * This function implements the save logic for the shipping.
     *
     * @param Object|Mixed $model
     * @param Object|Mixed $resourceModel
     * @param RequestInterface $request
     * @param Object|Mixed $profileCollection
     * @param Object|Mixed $secondaryModel
     * @param Object|Mixed $chargeResourceModel
     * @return mixed
     */
    public function saveLogic(
        $model,
        $resourceModel,
        $request,
        $profileCollection,
        $secondaryModel,
        $chargeResourceModel
    ) {
        $modelFactory = $model->create();
        $resourceModel->load($modelFactory, $request->getParam('profile_id'), 'profile_id');
        $modelFactory->setProfileName($request->getParam('profile_name'));
        $modelFactory->setModifiedAt($this->dateTime->formatDate(true));
        $resourceModel->save($modelFactory);
        $this->saveShippingCharges(
            $modelFactory,
            $profileCollection,
            $request,
            $secondaryModel,
            $chargeResourceModel
        );
        return $modelFactory;
    }

    /**
     * This function process the data needed to modified and saved.
     *
     * @param Object|Mixed $primaryModel
     * @param Object|Mixed $profileCollection
     * @param RequestInterface $request
     * @param Object|Mixed $secondaryModel
     * @param Object|Mixed $chargeResourceModel
     * @return void
     */
    public function saveShippingCharges(
        $primaryModel,
        $profileCollection,
        $request,
        $secondaryModel,
        $chargeResourceModel
    ) {
        $updatedData = [];
        $profileId = $primaryModel->getProfileId();
        $oldData = $this->prepareOldDataToCompare($profileCollection, $profileId);
        $newData = $this->prepareNewDataToCompare($request, $profileId);
        if (empty($oldData)) {
            $this
                ->insertValues(
                    $secondaryModel,
                    $chargeResourceModel,
                    $this
                        ->prepareNewData($request, $profileId)
                );
            return;
        }
        $delete = array_diff_key($oldData, $newData);
        $insert = array_diff_key($newData, $oldData);
        if (!empty($insert)) {
            foreach ($newData as $key => $newDatum) {
                if (!isset($insert[$key])) {
                    $updatedData[$key] = $newDatum;
                }
            }
        }
        if (empty($updatedData)) {
            $updatedData = $newData;
        }
        $update = $this
            ->compareUpdateData($updatedData, $oldData);
        $this
            ->deleteValues(
                $secondaryModel,
                $chargeResourceModel,
                $delete
            );
        $this->insertValues(
            $secondaryModel,
            $chargeResourceModel,
            $insert
        );
        $this
            ->updateValues(
                $secondaryModel,
                $chargeResourceModel,
                $update,
                $profileId
            );
    }

    /**
     * This function builds the shipping profile data for ui form.
     *
     * @param string $requestId
     * @param Object|Mixed $collection
     * @param array $loadedData
     * @return array
     */
    public function buildShippingProfile($requestId, $collection, $loadedData)
    {
        $collection->addFieldToFilter('profile_id', $requestId);
        foreach ($collection->getItems() as $value) {
            $loadedData[$requestId]['profile_id'] = $value->getProfileId();
            $loadedData[$requestId]['profile_name'] = $value->getProfileName();
        }
        return $loadedData;
    }

    /**
     * This function builds the dynamic rows needed for ui form.
     *
     * @param string $requestId
     * @param Object|Mixed $secondaryCollection
     * @param array $loadedData
     * @return array
     */
    public function buildDynamicRows($requestId, $secondaryCollection, $loadedData)
    {
        $data = [];
        $count = 0;
        $collection = $secondaryCollection->create();
        $collection->addFieldToFilter('profile_id', $requestId);
        foreach ($collection->getItems() as $value) {
            $data[$count] = $value->getData();
            $data[$count]['record_id'] = $count;
            $count++;
        }
        $loadedData[$requestId]['dynamic_rows'] = $data;
        return $loadedData;
    }
}
