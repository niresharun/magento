<?php

namespace Ziffity\Shipping\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Ziffity\Shipping\Model\Import\Process;
use Ziffity\Shipping\Model\OversizeProfile\ResourceModel\OversizeProfile;
use Ziffity\Shipping\Model\OversizeProfile\OversizeProfileFactory;
use Ziffity\Shipping\Model\OversizeProfileCharge\ProfileChargeFactory;
use Ziffity\Shipping\Model\OversizeProfileCharge\ResourceModel\ProfileCharge as OversizeProfileCharge;

class ImportOverSizeProfiles extends \Magento\Framework\App\Config\Value
{

    protected $overSizeModel;

    protected $chargeModel;

    /**
     * @var OversizeProfile
     */
    protected $overSizeProfile;

    /**
     * @var OversizeProfileCharge
     */
    protected $overSizeProfileCharge;

    /**
     * @var Process
     */
    protected $process;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param Process $process
     * @param OversizeProfile $overSizeProfile
     * @param OversizeProfileCharge $overSizeProfileCharge
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
        OversizeProfile $overSizeProfile,
        OversizeProfileFactory $overSizeModel,
        ProfileChargeFactory $chargeModel,
        OversizeProfileCharge $overSizeProfileCharge,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->process = $process;
        $this->overSizeProfile = $overSizeProfile;
        $this->overSizeModel = $overSizeModel;
        $this->chargeModel = $chargeModel;
        $this->overSizeProfileCharge = $overSizeProfileCharge;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        //TODO:Query to get the csv sheet
        //'SELECT * FROM adg_oversize_profile join adg_oversize_profile_charge on adg_oversize_profile.profile_id = adg_oversize_profile_charge.profile_id;'
        if (!empty($_FILES['groups']['tmp_name']['ziffity']['fields']['import_oversize_profiles']['value'])) {
            $filePath = $_FILES['groups']['tmp_name']['ziffity']['fields']['import_oversize_profiles']['value'];
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
                'united_inch_min' => $value['united_inch_min'],
                'united_inch_max' => $value['united_inch_max'],
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
        $model = $this->overSizeModel->create();
        $this->overSizeProfile->load($model,null,'profile_id');
        $model->setData($data);
        $this->overSizeProfile->save($model);
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
        $model = $this->chargeModel->create();
        $this->overSizeProfileCharge->load($model,null,'charge_id');
        $model->setData($data);
        $this->overSizeProfileCharge->save($model);
    }
}
