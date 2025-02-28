<?php

namespace Ziffity\SavedDesigns\Block;

use Magento\Framework\View\Element\Template\Context;
use Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns\CollectionFactory as saveddesignsncollection;
use Magento\Customer\Model\Session;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Checkout\Helper\Cart;
use Amasty\RequestQuote\Helper\Cart as QuoteCart;


class SavedDesigns extends \Magento\Framework\View\Element\Template
{

    const TOOLBARVIEW = 'saved_design/general/design_pageination';
    const SAVELIMIT = 'saved_design/general/max_save_limit';

	/**
     * @var \Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns\CollectionFactory
     */
    protected $designCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $helperImageFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepos;

    /** @var PriceCurrencyInterface $priceCurrency */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $checkoutHelper;
    protected $quoteHelper;

    /**
     * @param Context $context
     * @param saveddesignsncollection $designCollectionFactory
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Helper\ImageFactory $helperImageFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepos
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        saveddesignsncollection $designCollectionFactory,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\ImageFactory $helperImageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepos,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Cart $checkoutHelper,
        QuoteCart $quoteHelper
    ){
        $this->designCollectionFactory = $designCollectionFactory;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->helperImageFactory = $helperImageFactory;
        $this->assetRepos = $assetRepos;
        $this->priceCurrency = $priceCurrency;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutHelper = $checkoutHelper;
        $this->quoteHelper = $quoteHelper;

		parent::__construct($context);
	}

    /**
     * @return $this|SavedDesigns
     */
     protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->pageConfig->getTitle()->set(__('Saved Designs'));

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle(__('Saved Designs'));
        }

        if ($this->getDesignsCollection()) {
            $limit = explode(',',$this->getToolbarViews());
            $limiter = array();
            foreach ($limit as $value) {
                $limiter[$value] = $value;
            }
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'custom.saveddesign.pager'
            )->setAvailableLimit($limiter)
            ->setShowPerPage(true)->setCollection($this->getDesignsCollection());
            $this->setChild('pager', $pager);
            $this->getDesignsCollection()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get place holder image of a product for small_image
     *
     * @return string
     */
    public function getPlaceHolderImage()
    {
        $imagePlaceholder = $this->helperImageFactory->create();
        return $this->assetRepos->getUrl($imagePlaceholder->getPlaceholder('small_image'));
    }

    /**
     * @param $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct($productId)
    {
        return $product = $this->productRepository->getById($productId);
    }

    /**
     * Function getFormatedPrice
     *
     * @param float $price
     *
     * @return string
     */
    public function getFormatedPrice($amount)
    {
        return $this->priceCurrency->convertAndFormat($amount);
    }

    /**
     * @param null
     * @return object
     */
    public function getDesignsCollection()
    {
        $designCollection = $this->designCollectionFactory->Create();
        if ($this->customerSession->isLoggedIn()) {
            $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
            $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 8;
            $designCollection->addFieldToFilter('customer_id', $this->customerSession->getCustomer()->getId());
            $designCollection->setPageSize($pageSize);
            $designCollection->setCurPage($page);
            $designCollection->setOrder('updated_at','DESC');
            return $designCollection;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getToolbarViews() {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::TOOLBARVIEW, $storeScope);
    }

    /**
     * @return mixed
     */
    public function getCustomerLimit() {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::SAVELIMIT, $storeScope);
    }

    public function getAddToCartUrl($product)
    {
        $addToCartUrl = '';
        if($product){
            $addToCartUrl = $this->checkoutHelper->getAddUrl($product, []);
        }
        return $addToCartUrl;
    }

    public function getAddToQuotetUrl($product)
    {
        $addToQuoteUrl = '';
        if($product){
            $addToQuoteUrl = $this->quoteHelper->getAddUrl($product, []);
        }
        return $addToQuoteUrl;
    }
}
