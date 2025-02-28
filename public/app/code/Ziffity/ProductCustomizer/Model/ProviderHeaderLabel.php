<?php

namespace Ziffity\ProductCustomizer\Model;

use Magento\Framework\Registry;
use Ziffity\CustomFrame\Helper\Data;

class ProviderHeaderLabel implements ConfigProviderInterface
{

    protected $helper;

    protected $registry;

    protected $customizerConfig = [];

    public function __construct(Registry $registry,Data $helper)
    {
        $this->registry = $registry;
        $this->helper = $helper;
    }

    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getConfig()
    {
        $product = $this->getProduct();
        $options['headerLabel'] = $this->findHeaderLabel($product);
        $options['headerLabelStatus'] = $options['headerLabel'] !== null ? 1 : 0;
        return $options;
    }

    public function findHeaderLabel($product)
    {
        if ($this->helper->hasHeader($product->getSku())){
            return 'header';
        }
        if ($this->helper->hasLabel($product->getSku())){
            return 'label';
        }
        return null;
    }

    public function setConfig($config)
    {
        $this->customizerConfig = $config;
    }
}
