<?php

namespace Ziffity\RequestQuote\Block\Adminhtml\Quote\View\Items\Renderer;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Framework\UrlInterface;
use Amasty\RequestQuote\Block\Adminhtml\Quote\View\Items\Renderer\DefaultRenderer as AmastyRenderer;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Ziffity\ProductCustomizer\Helper\Selections;
use Magento\Catalog\Api\ProductRepositoryInterface;

class DefaultRenderer extends AmastyRenderer
{
    /**
     * @var \Magento\Checkout\Helper\Data
     */
    private $checkoutHelper;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var \Amasty\RequestQuote\Helper\Data
     */
    private $configHelper;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    protected $adminSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    protected $selectionsHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Amasty\RequestQuote\Model\Quote\Backend\Session $quoteSession,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\Tax\Model\Config $taxConfig,
        \Amasty\RequestQuote\Helper\Data $configHelper,
        PriceCurrencyInterface $priceCurrency,
        UrlInterface $urlBuilder,
        ProductUrl $productUrl,
        AdminSession $adminSession,
        Selections $selectionsHelper,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        $this->urlBuilder = $urlBuilder;
        $this->productUrl = $productUrl;
        $this->adminSession = $adminSession;
        $this->selectionsHelper = $selectionsHelper;
        $this->_authorization = $context->getAuthorization();
        $this->productRepository = $productRepository;
        parent::__construct(
            $context,
            $stockRegistry,
            $stockConfiguration,
            $quoteSession,
            $currencyFactory,
            $checkoutHelper,
            $serializer,
            $taxConfig,
            $configHelper,
            $priceCurrency,
            $data,
        );
    }

    /**
     * @param \Magento\Framework\DataObject|Item $item
     * @param string $column
     * @param null $field
     * @return string
     */
    public function getColumnHtml(\Magento\Framework\DataObject $item, $column, $field = null)
    {
        $html = '';
        switch ($column) {
            case 'product':
                if ($this->canDisplayContainer()) {
                    $html .= '<div id="' . $this->getHtmlId() . '">';
                }
                $html .= $this->getColumnHtml($item, 'name');
                if ($this->canDisplayContainer()) {
                    $html .= '</div>';
                }
                $completedSteps = $this->selectionsHelper->getCompletedStepsFromOptions($item->getAdditionalData());
                $data =  $this->selectionsHelper->getUnserializedData($item->getAdditionalData());
                $product = $this->productRepository->getById($item->getProductId());
                $selections  = $this->selectionsHelper->getSelections($data, $product, $completedSteps);
                $renderHtml = $this->renderSelections($selections);
                $html .= $renderHtml;
                break;
            case 'price-original':
                $html = $this->displayPriceAttribute('price');
                break;
            case 'product-price':
                $html = $this->displayProductPrice();
                break;
            case 'price':
                $html = $item->getCustomPrice()
                    ? $this->displayCustomPrice()
                    : $this->displayProductPrice($item->getQty());
                break;
            case 'qty':
                $html = $item->getQty() * 1;
                break;
            case 'subtotal':
                $html = $this->displayPriceAttribute('subtotal');
                break;
            case 'total':
                $code = $this->priceInclTax() ? 'row_total_incl_tax' : 'row_total';
                $html = $this->displayPriceAttribute($code);
                break;
            case 'cost':
                if ($cost = $item->getProduct()->getData($this->configHelper->getCostAttribute())) {
                    $html = $this->displayPrices(
                        $cost,
                        $this->getBaseCurrency()->convert($cost, $this->getCurrency()),
                        false,
                        '<br />'
                    );
                } else {
                    $html = '-';
                }
                break;
            case 'edit':
                $hasAccess = $this->_isAllowedAction('Amasty_RequestQuote::edit');
                if($hasAccess) {
                    $url = $this->getQuoteEditUrl($item);
                    $html = $this->editInFrontend($url);
                    $html.= "<a class='action action-edit' href='javascript:void(0)' onclick='loadFrontend(); return false;'>Edit in Frontend</a>";
                } else {
                    $html = '-';
                }
                break;
            default:
                $html = parent::getColumnHtml($item, $column, $field);
        }
        return $html;
    }

    public function editInFrontend($url)
    {
        $script = <<<SCRIPT
                        <script>
                            function loadFrontend(){
                                //alert('Button Clicked!');
                              var childWindow = window.open('$url', '_blank');
                              // childWindow.onbeforeunload = function(event) {
                              //   alert('Parent window is closing.');
                              //   childWindow.close();
                              //   };
                              childWindow.addEventListener('beforeunload', function(event) {
                                  event.preventDefault();
                                  window.location.reload();
                                    // Child window is closing
                                    console.log('Child window is closing');
                                });
                            }
                            // Your JavaScript code goes here
                            console.log('Hello, world!');
                        </script>
                        SCRIPT;

        return $script;
    }

    public function renderSelections($selections)
    {
        $html = '';
        if($selections){
            $html .= '<details><summary><strong>Show Product details</strong></summary><dl>';
            foreach($selections as $key => $selection){
                if(!isset($selection['label'])){
                    $html .= '<dt><strong>'.$key.'</strong></dt>';
                    $html .= '<dd>';
                    foreach ($selection as $key => $sel){
                        $html .= '<div>
                            <span>'.$sel["label"].'</span>:
                            <span>'.$sel["value"].'</span>
                       </div><br>';

                    }
                    $html .= '</dd>';
                } else {
                    $html .= '<dt><strong>'.$selection["label"].'</strong></dt><dd>'.$selection["value"].'</dd>';
                }

            }
            $html .= '</dl></details>';
        }

        return $html;

    }


    public function getQuoteEditUrl($item)
    {
        $url = '';
        if($item->getProductId()){
            $params = [
                'selection' => 'request_quote',
                'scope' => 'admin',
                'item_id' => $item->getId()
            ];
            $url = $this->getProductUrl($item->getProduct(), $params);
        }
        return $url;

    }

    /**
     * Get the frontend URL
     *
     * @return string
     */
    public function getFrontendUrl()
    {
        return $this->urlBuilder->getBaseUrl();
    }

    public function getProductUrl($product, $params = [])
    {
        $url = $this->productUrl->getUrl($product, ["_query" => $params]);
        return $url;
    }

     public function hasAccess($resource)
     {
         $currentAdminUser = $this->adminSession->getUser();

         if ($currentAdminUser) {
             $role = $currentAdminUser->getRole();
             return $role->getResources()->isAllowed($resource);
         }

         return false;
     }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }


}
