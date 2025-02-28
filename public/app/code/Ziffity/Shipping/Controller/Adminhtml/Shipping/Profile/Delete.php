<?php

namespace Ziffity\Shipping\Controller\Adminhtml\Shipping\Profile;

use Ziffity\Shipping\Model\ShippingProfile\ResourceModel\ShippingProfile as ResourceModel;
use Ziffity\Shipping\Model\ShippingProfile\ShippingProfileFactory as Model;
use Ziffity\Shipping\Helper\Data;
use Psr\Log\LoggerInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

class Delete extends Action
{

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Model
     */
    protected Model $model;

    /**
     * @var ResourceModel
     */
    protected ResourceModel $resourceModel;

    /**
     * @param Context $context
     * @param Model $model
     * @param ResourceModel $resourceModel
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Model $model,
        ResourceModel $resourceModel,
        Data $helper,
        LoggerInterface $logger
    ) {
        $this->model           = $model;
        $this->resourceModel   = $resourceModel;
        $this->helper          = $helper;
        $this->logger          = $logger;
        parent::__construct($context);
    }

    /**
     * Class Delete execute function.
     *
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            $profileId = $this->getRequest()->getParam('profile_id');
            if ($profileId) {
                $model = $this->model->create();
                $this->resourceModel->load($model, $profileId, 'profile_id');
                if (!$model->getProfileName()) {
                    throw new \Magento\Framework\Exception\NoSuchEntityException(
                        __('Shipping Profile with id %1 not found', $profileId)
                    );
                }
                $associatedProductIds = $this->helper->isAllowedToDelete(
                    'shipping_profile',
                    $model
                );
                if (!empty($associatedProductIds)) {
                    throw new \Magento\Framework\Exception\CouldNotDeleteException(
                        __(
                            'This profile "%1" is associated with product id(s) [%2] , please unassign before deleting',
                            $model->getProfileName(),
                            implode(",", $associatedProductIds)
                        )
                    );
                }
                $this->resourceModel->delete($model);
                $this->messageManager->addSuccessMessage(__(
                    'Shipping Profile with %1 Deleted',
                    $model->getProfileName()
                ));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->messageManager
                ->addErrorMessage(__($exception->getMessage()));
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
            $this->messageManager
                ->addErrorMessage(__("Shipping profile could not be deleted , please check the log for more details"));
        }

        return $this->resultRedirectFactory
            ->create()->setPath('*/shipping_profile/grid');
    }
}
