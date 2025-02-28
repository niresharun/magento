<?php
declare(strict_types=1);

namespace Ziffity\SavedDesigns\Helper;

use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as QuoteItemCollection;
use Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns\CollectionFactory;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Ziffity\SavedDesigns\Model\SavedDesigns;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;

/**
 * Saved Designs data helper
 *
 * @api
 *
 */
class Data extends AbstractHelper
{

    /**
     * @var SerializerInterface
     */
    protected $jsonSerializer;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var UrlRewriteCollectionFactory
     */
    protected $urlCollection;

    /**
     * @var QuoteItemCollection
     */
    protected $quoteItemCollection;

    const XML_PATH_lIMIT_SCOPE = 'saved_design/general/max_save_limit';

    /**
     * @var CollectionFactory
     */
    protected $savedDesignsCollection;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Context $context
     * @param CollectionFactory $savedDesignsCollection
     * @param Filesystem $filesystem
     * @param QuoteItemCollection $quoteItemCollection
     * @param UrlRewriteCollectionFactory $urlCollection
     * @param UrlInterface $urlBuilder
     * @param LayoutFactory $layoutFactory
     * @param SerializerInterface $jsonSerializer
     */
    public function __construct(
        Context $context,
        CollectionFactory $savedDesignsCollection,
        Filesystem $filesystem,
        QuoteItemCollection $quoteItemCollection,
        UrlRewriteCollectionFactory $urlCollection,
        UrlInterface $urlBuilder,
        LayoutFactory $layoutFactory,
        SerializerInterface $jsonSerializer
    ) {
        $this->savedDesignsCollection = $savedDesignsCollection;
        $this->filesystem = $filesystem;
        $this->quoteItemCollection = $quoteItemCollection;
        $this->urlCollection = $urlCollection;
        $this->urlBuilder = $urlBuilder;
        $this->layoutFactory = $layoutFactory;
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context);
    }

    /**
     * Retrieve Save Design limit scope
     *
     * @return int
     */
    public function getSaveLimitScope(): int
    {
        $limitScope = $this->scopeConfig->getValue(
            self::XML_PATH_lIMIT_SCOPE,
            ScopeInterface::SCOPE_STORE
        );
        return (int)$limitScope;
    }

    public function getFileNameFromQuote($itemId)
    {
        $imageFileName = null;
        $collection = $this->quoteItemCollection->create();
        $collection->addFieldToFilter('item_id',$itemId);
        $collection->addFieldToSelect('additional_data');
        if (!empty($collection->getItems())){
            try{
                $data = $collection->getFirstItem()->getAdditionalData();
                $data = $this->jsonSerializer->unserialize($data);
                if (isset($data['additional_data']['canvasData'])){
                    return $data['additional_data']['canvasData'];
                }
            }catch (\Exception $exception){
                return null;
            }
        }
        return $imageFileName;
    }

    public function getOriginalAdditionalData($additionalData)
    {
        try{
            $additionalData = $this->jsonSerializer->unserialize($additionalData);
            if (isset($additionalData['additional_data']['original_additional_data'])){
                return $additionalData['additional_data']['original_additional_data'];
            }
        } catch (\Exception $exception){
            return $this->jsonSerializer->serialize([]);
        }
        return $this->jsonSerializer->serialize($additionalData);
    }

    /**
     * This function finds the additional data using the share code.
     *
     * @param string|null $shareCode
     * @return null
     */
    public function findAdditionalData($shareCode)
    {
        $collection = $this->savedDesignsCollection->create();
        $collection->addFieldToFilter('share_code',$shareCode);
        if (count($collection) && $collection->getFirstItem()){
            return $this->jsonSerializer->unserialize($collection->getFirstItem()->getAdditionalData());
        }
        return null;
    }

    /**
     * This function finds the additional data using the share code.
     *
     * @param string|null $shareCode
     * @return null
     */
    public function getSavedDesign($shareCode)
    {
        $collection = $this->savedDesignsCollection->create();
        $collection->addFieldToFilter('share_code',$shareCode);
        if (count($collection) && $collection->getFirstItem()){
            return $collection->getFirstItem();
        }
        return null;
    }

    /**
     * This function builds the sharing URL for editing and copying and for email.
     *
     * @param string $url
     * @param string $shareCode
     * @param bool $editMode
     * @return mixed
     */
    public function buildShareUrl($url, $shareCode, $editMode = false)
    {
        if ($url) {
            return $this->urlBuilder->getUrl($url, ['_query' => [
                'selection' => 'saved_designs',
                'share_code' => $shareCode,
                'edit_mode' => $editMode]]);
        }
        return $this->urlBuilder->getBaseUrl();
    }

    /**
     * This function finds the URL key using the entity id.
     *
     * @param string|int $productId
     * @return null
     */
    public function findUrlRewrite($productId)
    {
        $collection = $this->urlCollection->create();
        $collection->addFieldToFilter('entity_id',$productId);
        if (count($collection) && $collection->getLastItem()) {
            return $collection->getLastItem()->getRequestPath();
        }
        return null;
    }

    /**
     * This function finds the product id using the share code from the collection.
     *
     * @param string $shareCode
     * @return null|string|int
     */
    public function findProductId($shareCode)
    {
        $collection = $this->savedDesignsCollection->create();
        $collection->addFieldToFilter('share_code',$shareCode);
        if (count($collection) && $collection->getFirstItem()) {
            $data = $collection->getFirstItem();
            return $this->findUrlRewrite($data->getProductId());
        }
        return null;
    }

    /**
     * This function finds the url using the share code.
     *
     * @param string $shareCode
     * @return string|null
     */
    public function findShareUrl($shareCode)
    {
        $url = $this->findProductId($shareCode);
        if ($url) {
            return $url;
        }
        return null;
    }

    public function getSavedDesignCollection($shareCode)
    {
        $collection = $this->savedDesignsCollection->create();
        $collection->addFieldToFilter('share_code',$shareCode);
        if (count($collection) && $collection->getFirstItem()){
            return $collection->getFirstItem();
        }
        return null;
    }

    /**
     * @param SavedDesigns $collection
     * @return int|void
     */
    public function getProductPrice($collection)
    {
        $price = 0;
        if ($collection->getAdditionalData())
        {
            $data = $this->jsonSerializer->unserialize($collection->getAdditionalData());
            if (isset($data['additional_data']['subtotal'])){
                $price = $data['additional_data']['subtotal'];
            }
        }
        return $price;
    }

    public function getTemplateContent(): string
    {
        $html = '';
        $layout = $this->layoutFactory->create(['cacheable' => false]);
        $layout->getUpdate()->load('ziffity_saveddesigns_pdf');
        $layout->generateXml();
        $layout->generateElements();
        $block = $layout->getBlock('share_pdf');
        if ($block) {
            $html .= $block->toHtml();
        }
        return $html;
    }
}
