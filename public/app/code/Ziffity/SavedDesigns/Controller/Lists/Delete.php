<?php
declare(strict_types=1);

namespace Ziffity\SavedDesigns\Controller\Lists;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Ziffity\SavedDesigns\Model\SavedDesignsAuthenticator;
use Ziffity\SavedDesigns\Model\SavedDesignsFactory;
use Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns as SavedDesignsResourceModel;
use Magento\Framework\Controller\Result\RedirectFactory;

/**
 * Controller for deleting a saving design.
 */
class Delete  implements ActionInterface
{

    protected $redirectFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SavedDesignsFactory
     */
    protected $savedDesignFactory;

    /**
     * @var SavedDesignsResourceModel
     */
    protected $savedDesignsResourceModel;

    /**
     * @var SavedDesignsAuthenticator
     */
    protected $savedDesignsAuthenticator;

    /**
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     * @param SavedDesignsFactory $savedDesignFactory,
     * @param SavedDesignsResourceModel $savedDesignsResourceModel,
     * @param SavedDesignsAuthenticator $savedDesignsAuthenticator
     */
    public function __construct(
        RequestInterface                               $request,
        ManagerInterface                               $messageManager,
        LoggerInterface                                $logger,
        SavedDesignsFactory                            $savedDesignFactory,
        SavedDesignsResourceModel                      $savedDesignsResourceModel,
        SavedDesignsAuthenticator                      $savedDesignsAuthenticator,
        RedirectFactory $redirectFactory
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->savedDesignFactory = $savedDesignFactory;
        $this->savedDesignsResourceModel = $savedDesignsResourceModel;
        $this->savedDesignsAuthenticator = $savedDesignsAuthenticator;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Delete saved design
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $result = $this->redirectFactory->create();
        $requestData = $this->request->getParams();
        try {
            if (empty($requestData['id'])) {
                $this->messageManager->addErrorMessage(__('Invalid Data.'));
                return $result->setRefererUrl();
            }
            $savedDesignObject = $this->savedDesignFactory->create();
            $this->savedDesignsResourceModel->load($savedDesignObject, $requestData['id']);

            $this->savedDesignsAuthenticator->isAllowedAction($savedDesignObject->getCustomerId());

            $this->savedDesignsResourceModel->delete($savedDesignObject);
            $this->messageManager->addSuccessMessage(__('Successfully deleted the record.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $result->setRefererUrl();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong. Please try again later.'));
            $this->logger->critical($e->getMessage());
            return $result->setRefererUrl();
        }
        return $result->setRefererUrl();
    }
}
