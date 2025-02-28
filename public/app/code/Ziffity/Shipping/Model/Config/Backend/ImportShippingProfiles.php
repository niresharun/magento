<?php

namespace Ziffity\Shipping\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Ziffity\Shipping\Model\Import\Process;
use Ziffity\Shipping\Model\ProfileCharge\ProfileChargeFactory as Model;
use Ziffity\Shipping\Model\ProfileCharge\ResourceModel\ProfileCharge as ResourceModel;
use Ziffity\Shipping\Model\ShippingProfile\ShippingProfileFactory as PrimaryModel;
use Ziffity\Shipping\Model\ShippingProfile\ResourceModel\ShippingProfile as PrimaryResourceModel;

class ImportShippingProfiles extends \Magento\Framework\App\Config\Value
{

    /**
     * @var PrimaryModel
     */
    protected $primaryModel;

    /**
     * @var PrimaryResourceModel
     */
    protected $primaryResource;

    /**
     * @var ResourceModel
     */
    protected $resourceModel;

    /**
     * @var Process
     */
    protected $process;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param Process $process
     * @param Model $model
     * @param ResourceModel $resourceModel
     * @param PrimaryModel $primaryModel
     * @param PrimaryResourceModel $primaryResource
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        Process $process,
        Model $model,
        ResourceModel $resourceModel,
        PrimaryModel $primaryModel,
        PrimaryResourceModel $primaryResource,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->process = $process;
        $this->model = $model;
        $this->resourceModel = $resourceModel;
        $this->primaryModel = $primaryModel;
        $this->primaryResource = $primaryResource;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        //TODO: Query to get the csv sheet
        //'SELECT * FROM adg_shipping_profile join adg_shipping_profile_charge on adg_shipping_profile.profile_id = adg_shipping_profile_charge.profile_id;'
        if (!empty($_FILES['groups']['tmp_name']['ziffity']['fields']['import_shipping_profiles']['value'])) {
            $filePath = $_FILES['groups']['tmp_name']['ziffity']['fields']['import_shipping_profiles']['value'];
            $csvFileContents = $this->process->getCsvFile($filePath);
            $data = $this->process->processData($csvFileContents);
            $this->importData($data);
        }
        return parent::afterSave();
    }

    /**
     * This function processes the data and inserts in tables.
     *
     * @param array $data
     * @return void
     * @throws AlreadyExistsException
     */
    public function importData($data)
    {
        $profileId = null;
        $oldProfileId = [];
        foreach ($data as $value)
        {
            if (!in_array($value['profile_id'],$oldProfileId)) {
                $profileId = $this->insertPrimaryTable(['profile_name'=>$value['profile_name']]);
                $oldProfileId[] = $value['profile_id'];
            }
            $this->insertSecondaryTable([
                'profile_id' => $profileId,
                'product_cost_min' => $value['order_subtotal_min'],
                'product_cost_max' => $value['order_subtotal_max'],
                'shipping_charge_type' => $value['shipping_charge_type'],
                'shipping_charge' =>$value['shipping_charge']]);
        }
    }

    /**
     * This function is responsible for loading model and resource model saving.
     *
     * @param array $data
     * @return mixed
     * @throws AlreadyExistsException
     */
    public function insertPrimaryTable($data)
    {
        $model = $this->primaryModel->create();
        $this->primaryResource->load($model,null,'profile_id');
        $model->setData($data);
        $this->primaryResource->save($model);
        return $model->getProfileId();
    }

    /**
     * This function is responsible for loading model and resource model saving.
     *
     * @param array $data
     * @return void
     * @throws AlreadyExistsException
     */
    public function insertSecondaryTable($data)
    {
        $model = $this->model->create();
        $this->resourceModel->load($model,null,'charge_id');
        $model->setData($data);
        $this->resourceModel->save($model);
    }
}
