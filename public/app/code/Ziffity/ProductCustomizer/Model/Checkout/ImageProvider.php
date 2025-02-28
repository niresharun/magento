<?php

namespace Ziffity\ProductCustomizer\Model\Checkout;

use Magento\Checkout\CustomerData\DefaultItem;
use Magento\Framework\App\ObjectManager;
use Magento\Checkout\Model\Cart\ImageProvider as CoreImageProvider;
use Ziffity\SavedDesigns\ViewModel\ProcessSaveDesigns;
use Ziffity\ProductCustomizer\Helper\Selections;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * @api
 * @since 100.0.2
 */
class ImageProvider
{
    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @var \Magento\Checkout\CustomerData\ItemPoolInterface
     * @deprecated 100.2.7 No need for the pool as images are resolved in the default item implementation
     * @see \Magento\Checkout\CustomerData\DefaultItem::getProductForThumbnail
     */
    protected $itemPool;

    /**
     * @var \Magento\Checkout\CustomerData\DefaultItem
     * @since 100.2.7
     */
    protected $customerDataItem;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface
     */
    private $itemResolver;

    /**
     * @var Selections
     */
    protected $selectionsHelper;

    /**
     * @var ProcessSaveDesigns
     */
    protected $savedImg;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $itemRepository
     * @param \Magento\Checkout\CustomerData\ItemPoolInterface $itemPool
     * @param DefaultItem|null $customerDataItem
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface $itemResolver
     */
    public function __construct(
        \Magento\Quote\Api\CartItemRepositoryInterface $itemRepository,
        \Magento\Checkout\CustomerData\ItemPoolInterface $itemPool,
        Selections $selectionsHelper,
        ProcessSaveDesigns $savedImg,
        ProductRepositoryInterface $productRepository,
        \Magento\Checkout\CustomerData\DefaultItem $customerDataItem = null,
        \Magento\Catalog\Helper\Image $imageHelper = null,
        \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface $itemResolver = null
    ) {
        $this->itemRepository = $itemRepository;
        $this->itemPool = $itemPool;
        $this->selectionsHelper = $selectionsHelper;
        $this->savedImg = $savedImg;
        $this->productRepository = $productRepository;
        $this->customerDataItem = $customerDataItem ?: ObjectManager::getInstance()->get(DefaultItem::class);
        $this->imageHelper = $imageHelper ?: ObjectManager::getInstance()->get(\Magento\Catalog\Helper\Image::class);
        $this->itemResolver = $itemResolver ?: ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getImages($cartId)
    {
        $itemData = [];

        /** @see code/Magento/Catalog/Helper/Product.php */
        $items = $this->itemRepository->getList($cartId);
        /** @var \Magento\Quote\Model\Quote\Item $cartItem */
        foreach ($items as $cartItem) {
            $itemData[$cartItem->getItemId()] = $this->getProductImageData($cartItem);
        }
        return $itemData;
    }

    /**
     * Get product image data
     *
     * @param \Magento\Quote\Model\Quote\Item $cartItem
     *
     * @return array
     */
    private function getProductImageData($cartItem)
    {
        $imageHelper = $this->imageHelper->init(
            $this->itemResolver->getFinalProduct($cartItem),
            'mini_cart_product_thumbnail'
        );
        $imgUrl = $imageHelper->getUrl();
        if($cartItem->getProductType() === "customframe"){
            $data =  $this->selectionsHelper->getUnserializedData($cartItem->getAdditionalData());
            if (
                !empty($data['additional_data']['canvasData']) &&
                $this->savedImg->checkImageExists($data['additional_data']['canvasData'])
            ) {
                $imgUrl = $this->savedImg->getImagePath($data['additional_data']['canvasData']);
            }
        }
        $imageData = [
            'src' => $imgUrl,
            'alt' => $imageHelper->getLabel(),
            'width' => $imageHelper->getWidth(),
            'height' => $imageHelper->getHeight(),
        ];
        return $imageData;
    }
}
