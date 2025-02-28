<?php
declare(strict_types=1);

namespace Ziffity\SavedDesigns\Controller\GetDetails;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NoSuchEntityException;
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
use Magento\Framework\Filesystem\Io\File as IoFileSystem;
use Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns\CollectionFactory;
use Ziffity\ProductCustomizer\Helper\Data as ProductCustomizerHelper;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Controller for processing and saving design of customframe product.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product implements \Magento\Framework\App\ActionInterface
{

    protected $helper;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

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

    protected $resultJsonFactory;


    /**
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param ProductRepositoryInterface $productRepository
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
        ProductRepositoryInterface                     $productRepository,
        RequestInterface                               $request,
        Json                                           $serializer,
        ManagerInterface                               $messageManager,
        CustomerSession                                $customerSession,
        ResponseInterface                              $response,
        LoggerInterface                                $logger,
        SavedDesignsFactory                            $savedDesignFactory,
        SavedDesignsResourceModel                      $savedDesignsResourceModel,
        Data                                           $helperData,
        ProductCustomizerHelper $helper,
        JsonFactory $resultJsonFactory,
    ) {
        $this->storeManager = $storeManager;
        $this->formKeyValidator = $formKeyValidator;
        $this->productRepository = $productRepository;
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
        $this->resultJsonFactory = $resultJsonFactory;
    }


    /**
     * Add product to saved design
     *
     * @return ResponseInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $resultData = [
            'success' => false
        ];

        $result = $this->resultJsonFactory->create();
        $customerId = $this->customerSession->getCustomerId();
        if (empty($customerId)) {
            $this->messageManager->addErrorMessage(
                __('Please login to save design.')
            );
            return $this->goBack($resultData);
        }
        $params = $this->request->getParams();
        unset($params['form_key']);
        try {
            if($params['designId']){
                $designId = $params['designId'];
                $savedDesign = $this->savedDesignFactory->create()->load($designId);
                $resultData['saved_design']  = $savedDesign->getAdditionalData();
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultData['error'] = $e->getMessage();
            return $this->goBack($resultData);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('The design isn\'t exist')
            );
            $this->logger->critical($e);
            $resultData['error'] = $e->getMessage();
        }
        return $result->setData($resultData);
    }

}
