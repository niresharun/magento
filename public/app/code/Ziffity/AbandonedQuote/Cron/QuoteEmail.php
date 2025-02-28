<?php

namespace Ziffity\AbandonedQuote\Cron;

use Amasty\RequestQuote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteFactoryCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as QuoteItemCollection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ziffity\AbandonedQuote\Model\ResourceModel\QuoteAbandoned\CollectionFactory as QuoteScheduledCollection;
use Ziffity\AbandonedQuote\Model\ResourceModel\QuoteAbandoned;
use Ziffity\AbandonedQuote\Model\QuoteAbandonedFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Area;

class QuoteEmail
{
    /**
     * @var $product
     */
    protected $productData=[];

    protected $collectionFactory;

    protected $storeManager;

    protected $serialize;

    protected $scopeConfig;

    protected $quoteCollection;

    protected $resource;

    protected $transportBuilder;

    protected $inlineTranslation;

    protected $quoteItemCollection;

    protected $productRepository;

    protected $quoteScheduledCollection;

    protected $quoteAbandoned;

    protected $quoteAbandonedModel;

    protected $modelQuoteRepository;

    public const ABANDONED_QUOTE_EMAIL_TEMPLATE = 'amasty_request_quote/quote_abandoned/email_template';

    public function __construct(
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        Json $serialize,
        ScopeConfigInterface $scopeConfig,
        QuoteFactoryCollection $quoteCollection,
        ResourceConnection $resource,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        QuoteItemCollection $quoteItemCollection,
        ProductRepositoryInterface $productRepository,
        QuoteScheduledCollection $quoteScheduledCollection,
        QuoteAbandoned $quoteAbandoned,
        QuoteAbandonedFactory $quoteAbandonedModel,
        QuoteRepository $modelQuoteRepository,
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->serialize = $serialize;
        $this->scopeConfig = $scopeConfig;
        $this->quoteCollection = $quoteCollection;
        $this->resource = $resource;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->quoteItemCollection = $quoteItemCollection;
        $this->productRepository = $productRepository;
        $this->quoteScheduledCollection = $quoteScheduledCollection;
        $this->quoteAbandoned = $quoteAbandoned;
        $this->quoteAbandonedModel = $quoteAbandonedModel;
        $this->modelQuoteRepository = $modelQuoteRepository;
    }

    /**
     * Write to system.log
     *
     * @return void
     */
    public function execute()
    {
        $allStores = $this->storeManager->getStores();
        foreach ($allStores as $store) {
            $storeId = $store->getId();
            $quoteValues = $this->scopeConfig->getValue(self::ABANDONED_QUOTE_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE, $storeId);
            $schedules = $this->serialize->unserialize($quoteValues);
            foreach ($schedules as $schedule) {
                $scheduleInterval = $schedule['send_after'];
                $lastRunSchedules = $this->quoteScheduledCollection->create()->addFieldToFilter('scheduled_interval', $scheduleInterval);
                $quoteModel = $this->quoteAbandonedModel->create();
                if (!$lastRunSchedules->count()) {
                    $resourceData = [
                        'scheduled_interval' => $scheduleInterval,
                        'last_run' => date('Y-m-d H:i:s')
                    ];
                    $quoteModel->setData($resourceData);
                    $this->quoteAbandoned->save($quoteModel);
                } else {
                    $lastRunSchedule = $lastRunSchedules->getFirstItem();
                    $lastRun = $lastRunSchedule->getLastRun();
                    $lastRunTime = strtotime($lastRun);
                    $lastRunTime = $lastRunTime - ($scheduleInterval * 3600);
                    $scheduledLastRun = date("Y-m-d H:i:s", $lastRunTime);
                    $date = date('Y-m-d H:i:s');
                    $time = strtotime($date);
                    $time = $time - ($scheduleInterval * 3600);
                    $currentTime = date("Y-m-d H:i:s", $time);
                    $collection = $this->collectionFactory->create()->addFieldToFilter('store_id',['eq' => $storeId])->addFieldToFilter('status', ['eq' => '0'])
                        ->addFieldToFilter('updated_at', ['from' => $scheduledLastRun, 'to' => $currentTime]);
                    $lastRunSchedule->setLastRun($date);
                    $lastRunSchedule->save();
                    foreach ($collection as $item) {
                        $productData = [];
                        $quoteId = $item->getEntityId();
                        $grandTotal = $item->getGrandTotal();
                        $quote = $this->modelQuoteRepository->get($quoteId);
                        $VisibleItemsInQuote = $quote->getAllVisibleItems();
                        foreach ($VisibleItemsInQuote as $productItems) {
                            $productQty = $productItems['qty'];
                            $productPrice = $productItems['price'];
                            $productId = $productItems->getProductId();
                            $product = $this->productRepository->getById($productId);
                            $store = $this->storeManager->getStore();
                            $productImage = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
                            $productData[] = ['id' => $productImage, 'name' => $product->getName(),
                                'qty' => $productQty, 'price' => $productPrice, 'image' => $productImage, 'total' => $grandTotal];
                        }
                        if (!($quote->getData()) == []) {
                            $this->sendMail($schedule['email'], $schedule['sender'], $item->getCustomerEmail(), $productData,$storeId);
                        }
                    }
                }
            }
        }
    }

    public function sendMail($emailTemplate,$senderName,$receiverEmail,$productData,$storeId): array
    {
        try{
            $templateId = $emailTemplate;

            $this->inlineTranslation->suspend();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $storeId,
                    ]
                )
                ->setTemplateVars([
                    'items' => $productData
                ])
                ->setFromByScope($senderName,$storeId)
                ->addTo($receiverEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            return ['is_error' => false, 'message' => 'Mail sent successfully'];
        } catch (Exception $e) {
            return ['is_error' => true, 'message' => $e->getMessage()];
        }
    }
}
