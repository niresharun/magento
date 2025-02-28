<?php
namespace Ziffity\Dashboard\Helper;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoreManagerInterface;

class ReferUrl extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $urlBuilder;

    protected $request;

    protected $storeManager;

    public function __construct(
        UrlInterface $urlBuilder,
        Context  $context,
        Http $request,
        StoreManagerInterface $storeManager
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getReferrerUrl()
    {
        $encodedUrl = $this->request->getParam('referer');

        if($encodedUrl){
            return base64_decode($encodedUrl);
        }
        else{
            return $this->storeManager->getStore()->getBaseUrl();
        }
    }
}
