<?php
declare(strict_types=1);

namespace Ziffity\SavedDesigns\Controller\Save;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Ziffity\SavedDesigns\Model\SavedDesignsFactory;
use Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns as SavedDesignsResourceModel;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Ziffity\SavedDesigns\Helper\Data;
use Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns\CollectionFactory;
use Ziffity\ProductCustomizer\Helper\Data as ProductCustomizerHelper;

/**
 * Controller for processing and saving design of customframe product.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveEdit implements HttpPostActionInterface
{

    /**
     * @var ProductCustomizerHelper
     */
    protected $helper;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var ResponseInterface
     */
    protected $response;

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
     * @var Data
     */
    protected $helperData;

    protected $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param RequestInterface $request
     * @param Json $serializer
     * @param ManagerInterface $messageManager
     * @param CustomerSession $customerSession
     * @param ResponseInterface $response
     * @param LoggerInterface $logger
     * @param SavedDesignsFactory $savedDesignFactory,
     * @param SavedDesignsResourceModel $savedDesignsResourceModel,
     * @param Data $helperData
     */
    public function __construct(
        StoreManagerInterface                          $storeManager,
        Validator                                      $formKeyValidator,
        RequestInterface                               $request,
        Json                                           $serializer,
        ManagerInterface                               $messageManager,
        CustomerSession                                $customerSession,
        ResponseInterface                              $response,
        LoggerInterface                                $logger,
        SavedDesignsFactory                            $savedDesignFactory,
        SavedDesignsResourceModel                      $savedDesignsResourceModel,
        Data                                           $helperData,
        ProductCustomizerHelper $helper
    ) {
        $this->storeManager = $storeManager;
        $this->formKeyValidator = $formKeyValidator;
        $this->request = $request;
        $this->serializer = $serializer;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->response = $response;
        $this->logger = $logger;
        $this->savedDesignFactory = $savedDesignFactory;
        $this->savedDesignsResourceModel = $savedDesignsResourceModel;
        $this->helperData = $helperData;
        $this->helper = $helper;
    }

    /**
     * Add product to saved design
     *
     * @return ResponseInterface|Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $resultData = [
            'success' => false
        ];

        if (!$this->formKeyValidator->validate($this->request)) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired.Please refresh the page')
            );
            return $this->goBack($resultData);
        }
        $customerId = $this->customerSession->getCustomerId();
        if (empty($customerId)) {
            $this->messageManager->addErrorMessage(
                __('Please login to save design.')
            );
            return $this->goBack($resultData);
        }
        try {
            $additionalData = $this->request->getParam('options');
            if ($additionalData && isset($additionalData['additional_data']['canvasData'])) {
                $imageFileName = $this->helper->generateImage($additionalData['additional_data']['canvasData'] ?? null);
                unset($additionalData['additional_data']['canvasData']);
                $additionalData = json_encode($additionalData);
            }
            $savedDesign = $this->savedDesignFactory->create();
            $this->savedDesignsResourceModel->load($savedDesign,$this->request->getParam('share_code'),'share_code');
            $savedDesign->setAdditionalData($additionalData);
            $savedDesign->setImageUrl($imageFileName);
            $this->savedDesignsResourceModel->save($savedDesign);
            $resultData['success'] = true;
            $this->messageManager->addSuccessMessage('Successfully saved the design');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultData['error'] = $e->getMessage();
            return $this->goBack($resultData);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this item to your saved design right now.')
            );
            $this->logger->critical($e);
            $resultData['error'] = $e->getMessage();
            return $this->goBack($resultData);
        }
        return $this->goBack($resultData);
    }

    /**
     * Resolve response
     *
     * @return ResponseInterface
     */
    protected function goBack($resultData)
    {
        $this->response->representJson($this->serializer->serialize($resultData));
        return $this->response;
    }

}
