<?php

namespace Ziffity\Shipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Rate\Result;
use Ziffity\Shipping\Model\OversizeCalculation;
use Magento\Checkout\Model\Session;
use Magento\Framework\Pricing\Helper\Data as Pricing;
use Ziffity\Shipping\Helper\Weight as WeightHelper;

class Shipping extends AbstractCarrier implements CarrierInterface
{

    public $weightHelper;

    protected $pricing;

    protected $checkoutSession;

    protected $shippingProfile;

    public $oversizeProfile;

    protected $oversizeCalculation;

    protected $orderSubtotal;

    protected $rateResultFactory;

    protected $rateMethodFactory;

    /**
     * Carrier Code.
     *
     * @var string
     */
    protected $_code = 'ziffity';

    public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
    \Psr\Log\LoggerInterface $logger,
    ResultFactory $rateResultFactory,
    MethodFactory $rateMethodFactory,
    CalculateShippingProfilePrice $shippingProfile,
    CalculateOversizeProfilePrice $oversizeProfile,
    OversizeCalculation $oversizeCalculation,
    Session $checkoutSession,
    Pricing $pricing,
    WeightHelper $weightHelper,
    array $data = [])
    {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->shippingProfile = $shippingProfile;
        $this->oversizeProfile = $oversizeProfile;
        $this->oversizeCalculation = $oversizeCalculation;
        $this->checkoutSession = $checkoutSession;
        $this->pricing = $pricing;
        $this->weightHelper = $weightHelper;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     *
     * @return false|bool|null|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();
        $result->append($this->getGroundRate($request));
        return $result;
    }

    protected function getGroundRate(RateRequest $request)
    {
        $totalWeight = 0;
        $this->orderSubtotal = $request->getPackageValue();
        $itemCharges = [];
        if ($request->getAllItems()) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($request->getAllItems() as $item) {
                $itemWeight = 0;
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if (!$child->getProduct()->isVirtual()) {
                            $itemWeight = $this->getItemWeight($item);
                            $itemCharges[] = $this->getGroundShippingProfilePrice($item->getProduct(), $child);
                        }
                    }
                } else if (!$item->getProduct()->isVirtual()) {
                    $itemWeight = $this->getItemWeight($item);
                    $itemCharges[] = $this->getGroundShippingProfilePrice($item->getProduct(), $item);
                }
                $totalWeight += $itemWeight;
            }
        }
        $price = array_sum($itemCharges);
        $oversized = $this->oversizeCalculation->calculate($this->checkoutSession->getQuote());
        $oversized_message = ($oversized > 0)
            ? " incl. " . $this->pricing->currencyByStore($oversized,null,true,false) . " oversized fee"
            : "";
        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $rate = $this->rateMethodFactory->create();
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('ground');
        $rate->setMethodTitle($this->getConfigData('ground_method_name') . $oversized_message);
        $rate->setPrice($price + $oversized);
        $rate->setCost(0);
        return $rate;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return float|int
     */
    protected function getGroundShippingProfilePrice($product, $item)
    {
        $profileId = null;
        $productModel = $this->weightHelper->productRepository->getById($product->getId(),false);
        $attribute = $productModel->getCustomAttribute('shipping_profile');
        if ($attribute) {
            $profileId = $attribute->getValue();
        }
        $price = $this->shippingProfile->getPrice($item->getBasePrice(), $profileId);
        $price = $price * $item->getQty();
        return $price;
    }

    /**
     * Calculate Item Weight.
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return float
     */
    protected function getItemWeight($item)
    {
        return $this->weightHelper->calculateQuoteItemWeight($item);
    }

    /**
     * Checks if rate is available.
     *
     * @param RateRequest $request
     *
     * @return bool
     */
    public function isGroundAvailable(RateRequest $request)
    {
        return true;
    }

    /**
     * Get allowed shipping methods.
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return [
            'ground'    => 'Ground Shipping',
            'expedited' => 'Expedited Shipping',
        ];
    }
}
