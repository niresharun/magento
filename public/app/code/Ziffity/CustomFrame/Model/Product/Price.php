<?php
namespace Ziffity\CustomFrame\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ziffity\ProductCustomizer\Model\CompositeConfigProvider;
use Ziffity\Coproduct\Model\ProcessRule;
use Magento\Catalog\Api\Data\ProductInterface;

class Price extends \Magento\Bundle\Model\Product\Price
{

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var array
     */
    protected $pricecomponents;

    protected $finalPrice;

    protected $compositeConfigProvider;

    protected $serializer;

    /**
     * Constructor
     *
     * @param \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param ProductTierPriceExtensionFactory|null $tierPriceExtensionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ProcessRule $processRule
     * @param array $pricecomponents
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        GroupManagementInterface $groupManagement,
        \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        ProductTierPriceExtensionFactory $tierPriceExtensionFactory = null,
        ProductRepositoryInterface $productRepository,
        CompositeConfigProvider $compositeConfigProvider,
        private ProcessRule $processRule,
        array $pricecomponents = []
    ) {
        $this->productRepository = $productRepository;
        $this->pricecomponents = $pricecomponents;
        $this->compositeConfigProvider = $compositeConfigProvider;
        $this->serializer = $serializer;
        parent::__construct(
            $ruleFactory,
            $storeManager,
            $localeDate,
            $customerSession,
            $eventManager,
            $priceCurrency,
            $groupManagement,
            $tierPriceFactory,
            $config,
            $catalogData,
            $serializer,
            $tierPriceExtensionFactory
        );
    }

    /**
     * Return custom frame product base price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param [] $selectionData
     * @return float
     */
    public function getPrice($product, $selectionData = null)
    {
        $price = 0;
        if (!empty($selectionData)) {
            $selectionData['parent_product'] = $product;
            foreach ($selectionData as $component => $selection) {
                $commonComponents = ['frame', 'glass', 'letter_board', 'post_finish', 'cork_board', 'chalk_board', 'dryerase_board', 'fabric', 'backing_board'];
                if (in_array($component, $commonComponents)) {
                    if (isset($selection['active_item']['id'])) {
                         $price += $this->getChildProductPrice($component, $selection['active_item']['id'], $selectionData);
                        continue;
                    }
                }

                switch ($component) {
                    case "mat":
                        if (isset($selection['active_items'])) {
                            foreach ($selection['active_items'] as $matType => $mat) {
                                if (isset($mat['id'])) {
                                    $price += $this->getChildProductPrice($component, $mat['id'], $selectionData);
                                }
                            }
                        }
                        break;
                    case "laminate_finish":
                        if (isset($selection['active_items']['laminate_exterior']['id'])) {
                            $price += $this->getChildProductPrice($component, $selection['active_items']['laminate_exterior']['id'], $selectionData);
                        }
                        if (isset($selection['active_items']['laminate_interior']['id'])) {
                            $price += $this->getChildProductPrice($component, $selection['active_items']['laminate_interior']['id'], $selectionData);
                        }
                        break;
                    case "accessories":
                        if (isset($selection['active_items'])) {
                            foreach ($selection['active_items'] as $item){
                                $associatedProduct = $this->productRepository->getById($item['id']);
                                $price += $associatedProduct->getPrice();
                            }
                        }
                        break;
                    case "shelves":
                        $price += $this->pricecomponents['shelves']->getPrice($selectionData);
                        break;
                    case "addons":
                        $price += $this->pricecomponents['addons']->getPrice($selection);
                        break;
                    case "crheader":
                        $price += $this->pricecomponents['header']->getPrice($selectionData);
                        break;
                    case "labels":
                        $price += $this->pricecomponents['lables']->getPrice($selectionData);
                        break;
                    case "lighting":
                        $price += $this->pricecomponents['lighting']->getPrice($selectionData);
                        break;
                }
            }
            $product =  $this->processRule->apply($product, $selectionData);
            if ($product->getCoProductPriceList()) {
                $price += array_sum($product->getCoProductPriceList());
            }
        }
        return $this->priceCurrency->round($price);
    }

    /**
     * Return custom frame product base price
     *
     * @param Product|ProductInterface $product
     * @param [] $selectionData
     * @return float|array
     */
    public function getPriceSummary($product, $selectionData = null)
    {
        $price = 0;
        $prices = [];
        $priceSummary = [];
        if (!empty($selectionData)) {
            $selectionData['parent_product'] = $product;
            foreach ($selectionData as $component => $selection) {
                $commonComponents = ['frame', 'glass', 'letter_board', 'post_finish', 'cork_board', 'chalk_board', 'dryerase_board', 'fabric', 'backing_board'];
                if (in_array($component, $commonComponents)) {
                    if (isset($selection['active_item']['id'])) {
                        $childprice = $this->getChildProductPrice($component, $selection['active_item']['id'],  $selectionData);
                        $price += $childprice;
                        $priceSummary[$component] = $this->priceCurrency->convertAndFormat($childprice, false, 2);
                        continue;
                    }
                }

                switch ($component) {
                    case "mat":
                        if (isset($selection['active_items'])) {
                            $childMatprice = 0;
                            foreach ($selection['active_items'] as $matType => $mat) {
                                if (isset($mat['id'])) {
                                    $childprice = $this->getChildProductPrice($component, $mat['id'], $selectionData);
                                    $price += $childprice;
                                    $childMatprice += $childprice;
                                    $priceSummary[$component] = $this->priceCurrency->convertAndFormat($childMatprice, false, 2);
                                }
                            }
                        }
                        break;
                    case "laminate_finish":
                        $laminateExtPrice = 0;
                        $laminateIntPrice = 0;
                        if (isset($selection['active_items']['laminate_exterior']['id'])) {
                            $laminateExtPrice = $this->getChildProductPrice($component, $selection['active_items']['laminate_exterior']['id'], $selectionData);
                            $price += $laminateExtPrice;
                        }
                        if (isset($selection['active_items']['laminate_interior']['id'])) {
                            $laminateIntPrice = $this->getChildProductPrice($component, $selection['active_items']['laminate_interior']['id'], $selectionData);
                            $price += $laminateIntPrice;
                        }
                        $priceSummary[$component] = $laminateExtPrice + $laminateIntPrice;
                        break;
                    case "accessories":
                        if (isset($selection['active_items'])) {
                            $accessoriesPrice = 0;
                            foreach ($selection['active_items'] as $item){
                                $associatedProduct = $this->productRepository->getById($item['id']);
                                $accessoriesPrice += $associatedProduct->getPrice();
                            }
                            $price += $accessoriesPrice;
                            $priceSummary[$component] = $this->priceCurrency->convertAndFormat($childprice, false, 2);;
                        }
                        break;
                    case "shelves":
                        $childprice = $this->pricecomponents['shelves']->getPrice($selectionData);
                        $price += $childprice;
                        if($childprice) {
                            $priceSummary[$component] = $this->priceCurrency->convertAndFormat($childprice, false, 2);;
                        }
                        break;
                    case "addons":
                        $childprice = $this->pricecomponents['addons']->getPrice($selection);
                        $price += $childprice;
                        if($childprice) {
                            $priceSummary[$component] = $this->priceCurrency->convertAndFormat($childprice, false, 2);;
                        }
                        break;
                    case "header":
                        $childprice = $this->pricecomponents['header']->getPrice($selectionData);
                        $price += $childprice;
                        $priceSummary[$component] = $this->priceCurrency->convertAndFormat($childprice, false, 2);;
                        break;
                    case "label":
                        $childprice = $this->pricecomponents['lables']->getPrice($selectionData);
                        $price += $childprice;
                        $priceSummary[$component] = $this->priceCurrency->convertAndFormat($childprice, false, 2);;
                        break;
                    case "lighting":
                        $childprice = $this->pricecomponents['lighting']->getPrice($selectionData);
                        $price += $childprice;
                        $priceSummary[$component] = $this->priceCurrency->convertAndFormat($childprice, false, 2);;
                        break;
                }
            }
            $product =  $this->processRule->apply($product, $selectionData);
            if ($product->getCoProductPriceList()) {
                $childprice = array_sum($product->getCoProductPriceList());
                $price += $childprice;
                $priceSummary['other_components'] = $this->priceCurrency->convertAndFormat($childprice, false, 2);
            }
        }
        $prices['subtotal'] = $this->priceCurrency->round($price);
        $prices['price_summary'] = $priceSummary;
        $prices['coproduct'] = $product->getCoProductDetails();
        return $prices;
    }

    /**
     * Return custom frame product base price
     *
     * @param Product|ProductInterface $product
     * @param [] $selectionData
     * @return float|array
     */
    public function getComponentPrice($product, $selectionData, $component)
    {
        $price = 0;
        $prices = [];
        $priceSummary = [];
        if (!empty($selectionData)) {
            switch ($component) {
                case "frame":
                    if (isset($selectionData[$component]['active_item']['id'])) {
                        $childprice = $this->getChildProductPrice($component, $selectionData[$component]['active_item']['id'],  $selectionData);
                        $priceSummary = $childprice;
                    }
                    break;
                case "mat":
                    if (isset($selectionData[$component]['active_items'])) {
                        $childMatprice = 0;
                        foreach ($selectionData[$component]['active_items'] as $matType => $mat) {
                            if (isset($mat['id'])) {
                                $childprice = $this->getChildProductPrice($component, $mat['id'], $selectionData);
                                $priceSummary[$matType] = $childprice;
                            }
                        }
                    }
                    break;
            }
        }
        return $priceSummary;
    }

    /**
     * Return Child product's calculated price
     *
     * @param string $component
     * @param int $associatedProductId
     * @param [] $selectionData
     * @return float
     */
    public function getChildProductPrice($component, $associatedProductId, $selectionData)
    {
        $associatedProduct = $this->productRepository->getById($associatedProductId);
        $priceModel = strtolower($component);
        if (isset($priceModel)) {
            return $this->pricecomponents[$priceModel]->getPrice($associatedProduct, $selectionData);
        }
        return 0;
    }

    public function getFinalPrice($qty, $product)
    {
       // clog('finalprice', 'exec');

        if($product->getTypeId() !== 'customframe') {
            if ($qty === null && $product->getCalculatedFinalPrice() !== null) {
                return $product->getCalculatedFinalPrice();
            }

            $finalPrice = $this->getBasePrice($product, $qty);
            $product->setFinalPrice($finalPrice);

            $this->_eventManager->dispatch('catalog_product_get_final_price', ['product' => $product, 'qty' => $qty]);

            $finalPrice = $product->getData('final_price');
            $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
            $finalPrice = max(0, $finalPrice);
            $product->setFinalPrice($finalPrice);

        } else {
            $finalPrice = $product->getMinPrice();
            $buyRequest = $product->getCustomOption('info_buyRequest');
            if ($buyRequest) {
                $data = $this->serializer->unserialize($product->getCustomOption('info_buyRequest')->getValue());
                $finalPrice = isset($data['price']) ? $data['price']: 0;
            }
//        return parent::getFinalPrice($qty, $product); // TODO: Change the autogenerated stub
            //$config = $this->compositeConfigProvider->getDefaultConfig($product, $skipPrice = true);
            //$finalPrice = $this->getPrice($product, $config['options']);
            //return $finalPrice;
        }

        return $finalPrice; //TODO get product price dunamically
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function getCustomOptionsPriceList($product)
    {
        // TODO: To plan for rule based prices of Frame Components & Parts*
        return [];
    }
}
