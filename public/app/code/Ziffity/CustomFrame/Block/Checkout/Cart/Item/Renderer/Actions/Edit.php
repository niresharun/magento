<?php

namespace Ziffity\CustomFrame\Block\Checkout\Cart\Item\Renderer\Actions;

use Amasty\RequestQuote\Model\UrlResolver;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Edit as CoreEdit;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Framework\UrlInterface;

class Edit extends CoreEdit
{
    /**
     * @var UrlResolver
     */
    private $urlResolver;

    protected $productUrl;

    public function __construct(
        Context $context,
        UrlResolver $urlResolver,
        ProductUrl $productUrl,
        UrlInterface $urlBuilder,
        array $data = []
    )
    {
        $this->urlResolver = $urlResolver;
        $this->productUrl = $productUrl;
        $this->urlBuilder = $urlBuilder;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @return string
     */
    public function getConfigureUrl()
    {
        $url = '';
        $item = $this->getItem();

        if($item->getProductId()){
            $params = [
                'selection' => 'checkout',
                'item_id' => $item->getId()
            ];
            $url = $this->getProductUrl($item->getProduct(), $params);
        }
        return $url;
    }

    public function getProductUrl($product, $params = [])
    {
        $url = $this->productUrl->getUrl($product, ["_query" => $params]);
        return $url;
    }
}
