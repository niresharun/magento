<?php

namespace Ziffity\ProductCustomizer\Model;

use \Ziffity\ProductCustomizer\Model\ConfigProviderInterface;
use Magento\Quote\Api\CartItemRepositoryInterface;
use \Magento\Quote\Model\Quote\ItemFactory;
use \Magento\Quote\Model\ResourceModel\Quote\Item;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\Request\Http;
use Ziffity\SavedDesigns\Helper\Data as SavedDesignsHelper;
use Magento\Framework\UrlInterface;

/**
 * Composite customframe configuration provider.
 */
class CompositeConfigProvider implements ConfigProviderInterface
{

    /**
     * @var SavedDesignsHelper
     */
    protected $savedDesignHelper;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var ConfigProviderInterface[]
     */
    private $configProviders;

    /**
     * @var CartItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    protected $quoteItemFactory;
    protected $itemResourceModel;

    protected $urlBuilder;
    /**
     * @param ConfigProviderInterface[] $configProviders
     */
    public function __construct(
        array $configProviders,
        ItemFactory $quoteItemFactory,
        Item $itemResourceModel,
        SerializerInterface $serializer,
        Http $request,
        SavedDesignsHelper $savedDesignHelper,
        UrlInterface $urlBuilder,
    ) {
        $this->configProviders = $configProviders;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->itemResourceModel = $itemResourceModel;
        $this->serializer = $serializer;
        $this->request = $request;
        $this->savedDesignHelper = $savedDesignHelper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        $data = '';
        $srcType = 'default';
        $quoteScope = '';
        $quoteItemId = null;
        $savedDesignId = null;

        $path = $this->request->getParam('selection');
        switch ($path) {
            case 'checkout':
                $quoteItemId = $this->request->getParam('item_id');
                $data = $this->getDataFromQuote($quoteItemId);
                $srcType = 'checkout';
                break;
            case 'request_quote':
                $quoteItemId = $this->request->getParam('item_id');
                $quoteScope = $this->request->getParam('scope');
                $data = $this->getDataFromQuote($quoteItemId);
                $srcType = 'request_quote';
                break;
            case 'saved_designs':
                $item = $this->savedDesignHelper->getSavedDesign($this
                    ->request->getParam('share_code'));
                $savedDesignId = $item->getId();
                $data = $this->savedDesignHelper->findAdditionalData($this
                    ->request->getParam('share_code'));
                $srcType = 'saved_designs';
                break;

        }
        $config['src_type'] = $srcType;
        $config['quote_scope'] = $quoteScope;
        if($quoteItemId) {
            $config['quote']['item_id'] = $quoteItemId;
            $quoteItem = $this->quoteItemFactory->create();
            $this->itemResourceModel->load($quoteItem, $quoteItemId);
            $config['quote']['qty'] = $quoteItem->getId() ? intval($quoteItem->getQty()):1;
        }
        if($savedDesignId){
            $config['saved_designs']['id'] = $savedDesignId;
//            $config['quote']['qty'] = $quoteItem->getId() ? intval($quoteItem->getQty()):1;
        }
        foreach ($this->configProviders as $configProvider) {
            //$data = $data ?: $config;
            $configProvider->setConfig($config);
            method_exists($configProvider, 'setFromData')? $configProvider->setFromData($data): '';
            $config = array_merge_recursive($config, $configProvider->getConfig());
        };
        return $config;
    }


    public function getDefaultConfig($product)
    {
        $config = [];
        if($product) {
            foreach ($this->configProviders as $configProvider) {
                //$data = $data ?: $config;
               // $configProvider->setSku($product->getSku);
                $configProvider->setConfig($config);
                method_exists($configProvider, 'setSku') ? $configProvider->setSku($product->getSku()) : '';
                $config = method_exists($configProvider, 'setSku') && method_exists($configProvider, 'prepareTab') ?
                    array_merge_recursive($config, $configProvider->getConfig()):
                    array_merge_recursive($config, []);
                //Wisset($tempconfig) ? array_merge($config, $tempconfig) : null;
            };
        }
        return $config;
    }


    public function getDataFromQuote($itemId)
    {
        $additionalData = '';
        if($itemId){
            $quoteItem = $this->quoteItemFactory->create();
            $this->itemResourceModel->load($quoteItem, $itemId);
            if($quoteItem->getId()){
                $additionalData = $this->serializer->unserialize($quoteItem->getAdditionalData());
            }
        }
        return $additionalData;
    }

    public function decodeQueryParams($queryParams)
    {
        $queryParams = $this->urlBuilder->getQueryParams();

        foreach ($queryParams as $key => $value) {
            $decodedValue = urldecode($value);
            $queryParams[$key] = $decodedValue;
        }

        return $queryParams;
    }
}
