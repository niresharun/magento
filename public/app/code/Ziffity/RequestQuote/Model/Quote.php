<?php

namespace Ziffity\RequestQuote\Model;

use Amasty\RequestQuote\Api\Data\QuoteInterface;
use Amasty\RequestQuote\Model\Email\AdminNotification;
use Amasty\RequestQuote\Helper\Data;
use Amasty\RequestQuote\Model\Quote\AdvancedMergeResult;
use Amasty\RequestQuote\Model\Quote\AdvancedMergeResultFactory;
use Amasty\RequestQuote\Model\Source\Status;
use Magento\Directory\Model\Currency;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Amasty\RequestQuote\Model\Quote as AmastyQuote;

class Quote extends AmastyQuote
{
    /**
     * @var array
     */
    private $customerFields = [
        'customer_id',
        'customer_tax_class_id',
        'customer_group_id',
        'customer_email',
        'customer_prefix',
        'customer_firstname',
        'customer_middlename',
        'customer_lastname',
        'customer_suffix',
        'customer_dob',
        'customer_note',
        'customer_note_notify',
        'customer_is_guest'
    ];

    /**
     * @var array
     */
    private $ignoreProductTypes = [
        'giftcard'
    ];

    /**
     * @var null|\Amasty\RequestQuote\Model\Source\Status
     */
    private $statusSource = null;

    /**
     * @var null|\Magento\Directory\Model\CurrencyFactory
     */
    private $currencyDirectoryFactory = null;

    /**
     * @var null|Currency
     */
    private $quoteCurrency = null;

    /**
     * @var null|Currency
     */
    private $baseCurrency = null;

    /**
     * @var null|ResolverInterface
     */
    private $localeResolver = null;

    /**
     * @var null|TimezoneInterface
     */
    private $timezone = null;

    /**
     * @var null|AdvancedMergeResultFactory
     */
    private $advancedMergeResultFactory = null;

    /**
     * @var null|Data
     */
    private $helper = null;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Amasty\RequestQuote\Model\ResourceModel\Quote::class);
        $this->statusSource = $this->_data['status_source'] ?? null;
        $this->currencyDirectoryFactory = $this->_data['currency_factory'] ?? null;
        $this->localeResolver = $this->_data['locale_resolver'] ?? null;
        $this->timezone = $this->_data['timezone'] ?? null;
        $this->advancedMergeResultFactory = $this->_data['advancedMergeResultFactory'] ?? null;
        $this->helper = $this->_data['helper'] ?? null;
    }

    public function getCreatedAtFormatted(int $format): string
    {
        return $this->formatDate($this->getCreatedAt(), $format);
    }

    public function getSubmitedDateFormatted(int $format): string
    {
        return $this->formatDate($this->getSubmitedDate(), $format);
    }

    private function formatDate(string $date, int $format): string
    {
        return $this->timezone->formatDateTime(
            new \DateTime($date),
            $format,
            $format,
            $this->localeResolver->getDefaultLocale(),
            $this->timezone->getConfigTimezone('store', $this->getStore())
        );
    }
    public function getStatusLabel()
    {
        return $this->statusSource->getStatusLabel($this->getStatus());
    }


    /**
     * @return Currency
     */
    public function getQuoteCurrency()
    {
        if ($this->quoteCurrency === null) {
            $this->quoteCurrency = $this->currencyDirectoryFactory->create();
            $this->quoteCurrency->load($this->getQuoteCurrencyCode());
        }

        return $this->quoteCurrency;
    }

    /**
     * Add product. Returns error message if product type instance can't prepare product.
     *
     * @param mixed $product
     * @param null|float|\Magento\Framework\DataObject $request
     * @param null|string $processMode
     * @return \Magento\Quote\Model\Quote\Item|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function addProduct(
        \Magento\Catalog\Model\Product $product,
                                       $request = null,
                                       $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
    ) {
        if ($request === null) {
            $request = 1;
        }
        if (is_numeric($request)) {
            $request = $this->objectFactory->create(['qty' => $request]);
        }
        if (!$request instanceof \Magento\Framework\DataObject) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }

        if (!$product->isSalable()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Product that you are trying to add is not available.')
            );
        }

        $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($request, $product, $processMode);

        /**
         * Error message
         */
        if (is_string($cartCandidates) || $cartCandidates instanceof \Magento\Framework\Phrase) {
            return (string)$cartCandidates;
        }

        /**
         * If prepare process return one object
         */
        if (!is_array($cartCandidates)) {
            $cartCandidates = [$cartCandidates];
        }

        $parentItem = null;
        $errors = [];
        $item = null;
        $items = [];
        foreach ($cartCandidates as $candidate) {
            // Child items can be sticked together only within their parent
            $stickWithinParent = $candidate->getParentProductId() ? $parentItem : null;
            $candidate->setStickWithinParent($stickWithinParent);

            $item = $this->getItemByProduct($candidate);
           // if (!$item) {
                $item = $this->itemProcessor->init($candidate, $request);
                $item->setQuote($this);
                $item->setOptions($candidate->getCustomOptions());
                $item->setProduct($candidate);
                // Add only item that is not in quote already
                $this->addItem($item);
          //  }
            $items[] = $item;

            /**
             * As parent item we should always use the item of first added product
             */
            if (!$parentItem) {
                $parentItem = $item;
            }
            if ($parentItem && $candidate->getParentProductId() && !$item->getParentItem()) {
                $item->setParentItem($parentItem);
            }

            $this->itemProcessor->prepare($item, $request, $candidate);

            // collect errors instead of throwing first one
            if ($item->getHasError()) {
                $this->deleteItem($item);
                foreach ($item->getMessage(false) as $message) {
                    if (!in_array($message, $errors)) {
                        // filter duplicate messages
                        $errors[] = $message;
                    }
                }
                break;
            }
        }
        if (!empty($errors)) {
            throw new \Magento\Framework\Exception\LocalizedException(__(implode("\n", $errors)));
        }

        $this->_eventManager->dispatch('sales_quote_product_add_after', ['items' => $items]);
        return $parentItem;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param bool $advancedMode
     * @param bool $inQuote
     */
    public function advancedMerge(\Magento\Quote\Model\Quote $quote, $advancedMode, $inQuote): AdvancedMergeResult
    {
        $warnings = [];
        $result = false;
        $itemsForRemove = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($inQuote) {
                if (in_array($item->getProductType(), $this->ignoreProductTypes)) {
                    $warnings[] = __('The Gift Card can not be converted from shopping cart to quote.');
                    continue;
                }
                $product = $this->productRepository->getById($item->getProductId(), true, $this->getStoreId());
                if ($product->getData(Data::ATTRIBUTE_NAME_HIDE_BUY_BUTTON) ||
                    !empty(array_uintersect(
                        $product->getCategoryIds(),
                        $this->helper->getExcludeCategories(),
                        'strcmp'
                    ))
                ) {
                    $warnings[] = __('One or several Products can not be converted from shopping cart to quote.');
                    continue;
                }
            }
            $result = true;
            $found = false;
            foreach ($this->getAllItems() as $quoteItem) {
                if ($quoteItem->compare($item)) {
                    $quoteItem->setQty($quoteItem->getQty() + $item->getQty());
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $newItem = clone $item;
                $this->addItem($newItem);
                if ($item->getHasChildren()) {
                    foreach ($item->getChildren() as $child) {
                        $newChild = clone $child;
                        $newChild->setParentItem($newItem);
                        $this->addItem($newChild);
                    }
                }
            }
            $itemsForRemove[] = $item->getItemId();
        }

        if ($result) {
            $this->setStoreId($quote->getStoreId());

            if ($advancedMode && count($this->getAllAddresses()) == 0) {
                foreach ($quote->getAllAddresses() as $address) {
                    $newAddress = clone $address;
                    $this->addAddress($newAddress);
                }

                foreach ($this->customerFields as $field) {
                    $value = $quote->getData($field);
                    $this->setData($field, $value);
                }
            }
            foreach ($itemsForRemove as $itemId) {
                $quote->removeItem($itemId);
            }
            $this->setTotalsCollectedFlag(false);
            $this->collectTotals();
        }

        return $this->advancedMergeResultFactory->create(['result' => $result, 'warnings' => $warnings]);
    }

    public function setQuoteName($quoteName)
    {
        $this->setData('quote_name', $quoteName);
        return $this;
    }
}
