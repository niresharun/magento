<?php

namespace Ziffity\Shipping\Controller\Adminhtml\Oversize\Profile;

use Ziffity\Shipping\Model\OversizeProfileCharge\ResourceModel\OversizeProfileCharge\CollectionFactory as ProfileChargeCollection;
use Ziffity\Shipping\Model\OversizeProfileCharge\ProfileChargeFactory as SecondaryModel;
use Ziffity\Shipping\Model\OversizeProfileCharge\ResourceModel\ProfileCharge as SecondaryResourceModel;
use Ziffity\Shipping\Model\OversizeProfile\ResourceModel\OversizeProfile as ResourceModel;
use Ziffity\Shipping\Model\OversizeProfile\OversizeProfileFactory as Model;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Ziffity\Shipping\Helper\Data;
use Exception;

class Save extends Action
{

    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * @var ProfileChargeCollection
     */
    protected ProfileChargeCollection $profileCollection;

    /**
     * @var SecondaryModel
     */
    protected SecondaryModel $secondaryModel;

    /**
     * @var SecondaryResourceModel
     */
    protected SecondaryResourceModel $chargeResourceModel;

    /**
     * @var Model
     */
    protected Model $model;

    /**
     * @var null
     */
    protected $modelFactory= null;

    /**
     * @var ResourceModel
     */
    protected ResourceModel $resourceModel;

    /**
     * @param Context $context
     * @param Model $model
     * @param ResourceModel $resourceModel
     * @param SecondaryModel $secondaryModel
     * @param SecondaryResourceModel $chargeResourceModel
     * @param ProfileChargeCollection $profileCollection
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Model $model,
        ResourceModel $resourceModel,
        SecondaryModel $secondaryModel,
        SecondaryResourceModel $chargeResourceModel,
        ProfileChargeCollection $profileCollection,
        Data $helper
    ) {
        $this->chargeResourceModel = $chargeResourceModel;
        $this->profileCollection = $profileCollection;
        $this->secondaryModel         = $secondaryModel;
        $this->resourceModel          = $resourceModel;
        $this->helper = $helper;
        $this->model                  = $model;
        parent::__construct($context);
    }

    /**
     * Class Save execute method.
     *
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            $modelFactory = null;
            $redirect     = $this->resultRedirectFactory->create();
            $modelFactory = $this->helper
                ->saveLogic(
                    $this->model,
                    $this->resourceModel,
                    $this->getRequest(),
                    $this->profileCollection,
                    $this->secondaryModel,
                    $this->chargeResourceModel
                );
            if (isset($modelFactory) && $modelFactory->getProfileId()) {
                $this->messageManager->addSuccessMessage(__('Oversize Profile have been saved !'));
            }
            if ($this->getRequest()->getParam('back') === 'close') {
                return $redirect->setPath('*/oversize_profile/grid');
            }
        } catch (Exception $exception) {
            $this->getMessageManager()->addErrorMessage($exception->getMessage());
        }
        if (isset($modelFactory) && $modelFactory->getProfileId()) {
            return $redirect
                ->setPath(
                    '*/*/edit',
                    [
                        'profile_id' => $modelFactory->getProfileId(),
                        '_current'   => true,
                    ]
                );
        }
        return $redirect->setPath($this->_redirect->getRefererUrl());
    }
}
