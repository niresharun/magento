<?php
declare(strict_types=1);

namespace Ziffity\SavedDesigns\Controller\Lists;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Ziffity\SavedDesigns\Model\SavedDesignsAuthenticator;
use Ziffity\SavedDesigns\Model\SavedDesignsFactory;
use Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns as SavedDesignsResourceModel;


class Duplicate implements HttpPostActionInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

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
     * @param JsonFactory $resultJsonFactory
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     * @param SavedDesignsFactory $savedDesignFactory,
     * @param SavedDesignsResourceModel $savedDesignsResourceModel,
     * @param SavedDesignsAuthenticator $savedDesignsAuthenticator
     */
    public function __construct(
        RequestInterface                               $request,
        JsonFactory                                    $resultJsonFactory,
        ManagerInterface                               $messageManager,
        LoggerInterface                                $logger,
        SavedDesignsFactory                            $savedDesignFactory,
        SavedDesignsResourceModel                      $savedDesignsResourceModel,
        SavedDesignsAuthenticator                      $savedDesignsAuthenticator
    ) {
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->savedDesignFactory = $savedDesignFactory;
        $this->savedDesignsResourceModel = $savedDesignsResourceModel;
        $this->savedDesignsAuthenticator = $savedDesignsAuthenticator;
    }

    /**
     * Duplicate saved design
     *
     * @return JsonFactory
     */
    public function execute()
    {

        $result = $this->resultJsonFactory->create();
        $requestData = $this->request->getParams();
        try {
            if (empty($requestData['id'])) {
                $this->messageManager->addErrorMessage(__('Invalid Data.'));
                return $result;
            }

            $savedDesignObject = $this->savedDesignFactory->create();
            $this->savedDesignsResourceModel->load($savedDesignObject, $requestData['id']);

            $this->savedDesignsAuthenticator->isAllowedAction($savedDesignObject->getCustomerId());

            $savedDesignObject->unsetData('entity_id');
            $savedDesignObject->unsetData('created_at');
            $savedDesignObject->unsetData('updated_at');
            $savedDesignObject->setTitle('Copy of '.$savedDesignObject->getTitle());
            $this->savedDesignsResourceModel->save($savedDesignObject);
            $this->messageManager->addSuccessMessage(__('Successfully duplicated the record.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $result;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong. Please try again later.'));
            $this->logger->critical($e->getMessage());
            return $result;
        }
        return $result;
    }
}
