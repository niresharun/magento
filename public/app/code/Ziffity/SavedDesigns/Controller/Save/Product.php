<?php
declare(strict_types=1);

namespace Ziffity\SavedDesigns\Controller\Save;

use Magento\Catalog\Api\Data\ProductInterface;
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

/**
 * Controller for processing and saving design of customframe product.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product implements HttpPostActionInterface
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
        ProductCustomizerHelper $helper
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
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false|ProductInterface
     */
    protected function _initProduct()
    {
        $productId = (int)$this->request->getParam('product');
        if ($productId) {
            $storeId = $this->storeManager->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
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
        $params = $this->request->getParams();
        unset($params['form_key']);
        $storeId = $this->storeManager->getStore()->getId();
        try {

            $product = $this->_initProduct();

            /** Check product availability */
            if (!$product) {
                $this->messageManager->addWarningMessage(__('Product not found.'));
                return $this->goBack($resultData);
            }
            if (isset($params['options']['additional_data']['canvasData'])) {
                $canvasImage = $params['options']['additional_data']['canvasData'];
            }
            $imageFileName = $this->helper->generateImage($canvasImage ?? null);
            unset($params['image']);
            if (isset($params['options']['additional_data']['canvasData'])){
                $params['options']['additional_data']['canvasData'] = $imageFileName;
            }
            $savedDesignData = [
                'title' => $product->getName(),
                'customer_id' => $customerId,
                'image_url' => $imageFileName,
                'product_id' => $params['product'],
                'store_id' => $storeId,
                'additional_data'=> json_encode($params['options']),
                'product_options' => '{"qty":1}'
            ];
            $savedDesign = $this->savedDesignFactory->create();
            $savedDesign->setData($savedDesignData);
            $this->savedDesignsResourceModel->save($savedDesign);

            $successMessage = __(
                'You added %1 to your saved designs.',
                $product->getName()
            );
            $this->messageManager->addSuccessMessage($successMessage);
            $resultData['success'] = true;
            $resultData['id'] = $savedDesign->getEntityId();
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
