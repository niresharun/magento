<?php

namespace Ziffity\SavedDesigns\Controller\Save;

use Ziffity\SavedDesigns\Model\SavedDesignsFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns as SavedDesignsResourceModel;
use Ziffity\SavedDesigns\Model\SavedDesignsAuthenticator;

class Delete implements HttpPostActionInterface
{

    /**
     * @var SavedDesignsResourceModel
     */
    protected $savedDesignsResourceModel;

    /**
     * @var SavedDesignsAuthenticator
     */
    protected $savedDesignsAuthenticator;

    /**
     * @var SavedDesignsFactory
     */
    protected $savedDesignFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param JsonFactory $jsonFactory
     * @param RequestInterface $request
     * @param SavedDesignsFactory $savedDesignFactory
     * @param SavedDesignsResourceModel $savedDesignsResourceModel
     * @param SavedDesignsAuthenticator $savedDesignsAuthenticator
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        JsonFactory $jsonFactory,
        RequestInterface $request,
        SavedDesignsFactory $savedDesignFactory,
        SavedDesignsResourceModel $savedDesignsResourceModel,
        SavedDesignsAuthenticator $savedDesignsAuthenticator,
        ManagerInterface $messageManager
    )
    {
        $this->jsonFactory = $jsonFactory;
        $this->request = $request;
        $this->savedDesignFactory = $savedDesignFactory;
        $this->savedDesignsResourceModel = $savedDesignsResourceModel;
        $this->savedDesignsAuthenticator = $savedDesignsAuthenticator;
        $this->messageManager = $messageManager;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        if ($this->request->getParam('id')) {
            try {
                $savedDesignObject = $this->savedDesignFactory->create();
                $this->savedDesignsResourceModel
                    ->load($savedDesignObject, $this->request->getParam('id'));
                $this->savedDesignsAuthenticator
                    ->isAllowedAction($savedDesignObject->getCustomerId());
                $this->savedDesignsResourceModel->delete($savedDesignObject);
                $this->messageManager
                    ->addSuccessMessage(__('Successfully removed from saved designs.'));
                return $this->jsonFactory->create()->setData(['success' => true]);
            } catch (\Exception $exception) {
                return $this->jsonFactory->create()->setData([
                    'success' => false,
                    'error' => $exception->getMessage()
                ]);
            }
        }
        return $this->jsonFactory->create()->setData(['success'=>false]);
    }
}
