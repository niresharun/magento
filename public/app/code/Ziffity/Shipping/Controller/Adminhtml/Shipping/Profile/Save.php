<?php

namespace Ziffity\Shipping\Controller\Adminhtml\Shipping\Profile;

use Ziffity\Shipping\Model\ProfileCharge\ResourceModel\ProfileCharge\CollectionFactory as ProfileChargeCollection;
use Ziffity\Shipping\Model\ProfileCharge\ResourceModel\ProfileCharge as SecondaryResourceModel;
use Ziffity\Shipping\Model\ShippingProfile\ResourceModel\ShippingProfile as ResourceModel;
use Ziffity\Shipping\Model\ProfileCharge\ProfileChargeFactory as SecondaryModel;
use Ziffity\Shipping\Model\ShippingProfile\ShippingProfileFactory as Model;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Ziffity\Shipping\Helper\Data;
use Exception;
use Magento\Framework\Exception\AlreadyExistsException;

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
     * @var ResourceModel
     */
    protected ResourceModel $resourceModel;

    /**
     * @var Context
     */
    protected Context $context;

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
        $this->context                = $context;
        $this->model                  = $model;
        $this->helper = $helper;
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
            $redirect     = $this->context
                ->getResultRedirectFactory()->create();
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
                $this->messageManager->addSuccessMessage(__('Shipping Profile have been saved !'));
            }
            if ($this->getRequest()->getParam('back') === 'close') {
                return $redirect->setPath('*/shipping_profile/grid');
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
        return $redirect->setPath($this->context->getRedirect()
            ->getRefererUrl());
    }
}
