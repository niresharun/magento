<?php
declare(strict_types=1);

namespace Ziffity\SavedDesigns\Controller\Save;

use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Amasty\RequestQuote\Api\QuoteItemRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Ziffity\SavedDesigns\Model\SavedDesignsFactory;
use Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns as SavedDesignsResourceModel;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Ziffity\SavedDesigns\Helper\Data;
use Amasty\RequestQuote\Model\Quote\Session as RequestQuoteSession;

/**
 * Controller for processing and saving design of customframe cart item.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartItem implements HttpPostActionInterface
{

    protected $redirectFactory;

    protected $requestQuoteRepository;

    protected $requestQuote;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

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
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var SavedDesignsFactory
     */
    protected $savedDesignFactory;

    /**
     * @var SavedDesignsResourceModel
     */
    protected $savedDesignsResourceModel;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param RequestInterface $request
     * @param Json $serializer
     * @param ManagerInterface $messageManager
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param ResultFactory $resultFactory
     * @param LoggerInterface $logger
     * @param SavedDesignsFactory $savedDesignFactory
     * @param SavedDesignsResourceModel $savedDesignsResourceModel
     * @param Data $helper
     */
    public function __construct(
        StoreManagerInterface                          $storeManager,
        Validator                                      $formKeyValidator,
        RequestInterface                               $request,
        Json                                           $serializer,
        ManagerInterface                               $messageManager,
        CustomerSession                                $customerSession,
        CheckoutSession                                $checkoutSession,
        ResultFactory                                  $resultFactory,
        LoggerInterface                                $logger,
        SavedDesignsFactory                            $savedDesignFactory,
        SavedDesignsResourceModel                      $savedDesignsResourceModel,
        Data $helper,Cart $cart,RequestQuoteSession $requestQuote,
        QuoteItemRepositoryInterface $requestQuoteRepository,
        RedirectFactory $redirectFactory
    ) {
        $this->storeManager = $storeManager;
        $this->formKeyValidator = $formKeyValidator;
        $this->request = $request;
        $this->serializer = $serializer;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
        $this->savedDesignFactory = $savedDesignFactory;
        $this->savedDesignsResourceModel = $savedDesignsResourceModel;
        $this->helper = $helper;
        $this->cart = $cart;
        $this->requestQuote = $requestQuote;
        $this->requestQuoteRepository = $requestQuoteRepository;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Add product to saved design
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->request)) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired.Please refresh the page')
            );
            return $this->goBack();
        }
        $customerId = $this->customerSession->getCustomerId();
        if (empty($customerId)) {
            $this->messageManager->addErrorMessage(
                __('Please login to save design.')
            );
            return $this->goBack();
        }
        $itemId = (int)$this->request->getParam('item');
        $requestQuote = $this->request->getParam('request_quote');
        try {
            if ($requestQuote === "false" || $requestQuote === false) {
                $item = $this->checkoutSession->getQuote()->getItemById($itemId);
            }
            if ($requestQuote === "true" || $requestQuote === true){
                $item = $this->requestQuote->getQuote()->getItemById($itemId);
            }
            $buyRequest = $item->getBuyRequest()->getData();
            unset($buyRequest['uenc']);
            unset($buyRequest['original_qty']);
            unset($buyRequest['action']);
            if (!$item) {
                throw new LocalizedException(
                    __("The cart item doesn't exist.")
                );
            }
            $storeId = $this->storeManager->getStore()->getId();
            $savedDesign = $this->savedDesignFactory->create();
            $imageFileName = $this->helper->getFileNameFromQuote($itemId);
            $savedDesignData = [
                'title' => $item->getName(),
                'customer_id' => $customerId,
                'image_url' => $imageFileName,
                'product_id' => $item->getProductId(),
                'store_id' => $storeId,
                'additional_data'=> $this->helper->getOriginalAdditionalData($item->getAdditionalData()),
                'product_options' => $this->serializer->serialize($buyRequest)
            ];
            $savedDesign->setData($savedDesignData);
            $this->savedDesignsResourceModel->save($savedDesign);
            $successMessage = __(
                'You moved %1 to your saved designs.',
                $item->getName()
            );
            $this->messageManager->addSuccessMessage($successMessage);
            if ($requestQuote === "true" || $requestQuote === true){
                return $this->deleteRequestQuoteCartItem($itemId);
            }
            if ($requestQuote === "false" || $requestQuote === false) {
                $this->deleteCartItem($itemId);
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->goBack();
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this item to your saved design right now.')
            );
            $this->logger->critical($e->getMessage());
            return $this->goBack();
        }
        return $this->goBack();
    }

    /**
     * @param $itemId
     * @return void
     */
    public function deleteCartItem($itemId)
    {
        try {
            $this->cart->removeItem($itemId);
            // We should set Totals to be recollected once more because of Cart model as usually is loading
            // before action executing and in case when triggerRecollect setted as true recollecting will
            // execute and the flag will be true already.
            $this->cart->getQuote()->setTotalsCollectedFlag(false);
            $this->cart->save();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t remove the item.'));
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * @param $cartId
     * @param $id
     * @return Redirect
     */
    public function deleteRequestQuoteCartItem($id)
    {
        $pathParams = ['form_key'=>$this->request->getParam('form_key'),
            'id'=>$id];
        return $this->redirectFactory->create()
            ->setPath('amasty_quote/cart/delete',$pathParams);
    }

    /**
     * Resolve response
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function goBack()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('checkout/cart');
    }

    protected function checkIfRequestQuote()
    {
        return true;
    }

}
