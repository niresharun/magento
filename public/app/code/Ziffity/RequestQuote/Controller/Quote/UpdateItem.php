<?php

namespace Ziffity\RequestQuote\Controller\Quote;

use Amasty\RequestQuote\Controller\Cart\UpdateItemOptions;
use Amasty\RequestQuote\Model\Email\AdminNotification;
use Amasty\RequestQuote\Model\HidePrice\Provider as HidePriceProvider;
use Amasty\RequestQuote\Model\Registry;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Filter\LocalizedToNormalized;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Amasty\RequestQuote\Model\QuoteRepository;
use \Magento\Quote\Model\Quote\ItemFactory;
use \Magento\Quote\Model\ResourceModel\Quote\Item;
use Magento\Framework\Serialize\SerializerInterface;

class UpdateItem extends UpdateItemOptions
{

    protected $quoteRepository;
    protected $quoteItemFactory;
    protected $itemResourceModel;
    protected $serializer;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\RequestQuote\Model\Quote\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Amasty\RequestQuote\Model\Cart $cart,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\EncoderInterface $encoder,
        \Amasty\RequestQuote\Helper\Cart $cartHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Amasty\RequestQuote\Model\Email\Sender $emailSender,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Amasty\RequestQuote\Helper\Data $configHelper,
        AdminNotification $adminNotification,
        AccountManagementInterface $accountManagement,
        CustomerUrl $customerUrl,
        AuthenticationInterface $authentication,
        CookieMetadataFactory $cookieMetadataFactory,
        PhpCookieManager $cookieManager,
        HidePriceProvider $hidePriceProvider,
        TimezoneInterface $timezone,
        CustomerExtractor $customerExtractor,
        \Psr\Log\LoggerInterface $logger,
        Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Amasty\RequestQuote\Model\UrlResolver $urlResolver,
        LocalizedToNormalized $localizedToNormalized = null,
        QuoteRepository $quoteRepository,
        ItemFactory $quoteItemFactory,
        Item $itemResourceModel,
        SerializerInterface $serializer
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->itemResourceModel = $itemResourceModel;
        $this->serializer = $serializer;
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $localeResolver,
            $resultPageFactory,
            $encoder,
            $cartHelper,
            $dataObjectFactory,
            $emailSender,
            $customerSessionFactory,
            $priceCurrency,
            $configHelper,
            $adminNotification,
            $accountManagement,
            $customerUrl,
            $authentication,
            $cookieMetadataFactory,
            $cookieManager,
            $hidePriceProvider,
            $timezone,
            $customerExtractor,
            $logger,
            $registry,
            $dateTime,
            $urlResolver,
            $localizedToNormalized
        );
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = [];
        }
        try {
            if (isset($params['qty'])) {
                $params['qty'] = $this->getLocateFilter()->filter($params['qty']);
            }

            if($id){
                $quoteItem = $this->quoteItemFactory->create();
                $this->itemResourceModel->load($quoteItem, $id);
                $quote = $this->quoteRepository->get($quoteItem->getQuoteId());
                $this->cart->setQuote($quote);
            }
            $quoteItem = $this->cart->getQuote()->getItemById($id);

            if($quoteItem){
                $quoteItem->setQty($params['qty']);
                $quoteItem->setAdditionalData($this->serializer->serialize($params['options']));

                if($quoteItem->getProduct()){
                    $buyRequest = $quoteItem->getProduct()->getCustomOption('info_buyRequest');
                    if($params['options']){
                        $price = floatval($params['options']['additional_data']['subtotal']);
                        $quoteItem->setBasePrice($price);
                        $quoteItem->setPrice($price);
                        $quoteItem->setRowTotal($price *  $quoteItem->getQty());
                        $quoteItem->setBaseRowTotal($price *  $quoteItem->getQty());
                        $quoteItem->setCustomPrice($price);
                        $quoteItem->setOriginalCustomPrice($price);
                        $buyRequest->unsetData('options');
                        $buyRequestData = $this->serializer->unserialize($buyRequest->getValue());
                        $buyRequestData['price'] = $price;
                        $buyRequest->setValue($this->serializer->serialize($buyRequestData));
                        $quoteItem->getProduct()->addCustomOption('info_buyRequest', $this->serializer->serialize($buyRequest->getData()));
                    }
                }
                $quoteItem->save();
                $quote->save();
            }
            if (!$quoteItem) {
                throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t find the quote item.'));
            }


            $this->_eventManager->dispatch(
                'checkout_cart_update_item_complete',
                ['item' => $quoteItem, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );
            if (!$this->checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    $message = __(
                        '%1 was updated in your quote cart.',
                        $this->getEscaper()->escapeHtml($quoteItem->getProduct()->getName())
                    );
                    $this->messageManager->addSuccessMessage($message);
                }
                return $this->resultRedirectFactory->create()->setPath('request_quote/cart/index');
            }
        }  catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t update the item right now.'));
            return $this->_goBack();
        }
        return $this->resultRedirectFactory->create()->setPath('*/*');
    }
}
