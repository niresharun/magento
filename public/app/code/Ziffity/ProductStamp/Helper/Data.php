<?php

namespace Ziffity\ProductStamp\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;


class Data extends AbstractHelper
{
    const FRAMESTATUS = 'swatch/image_serializer/module_status';
    const FRAMECONFIG = 'swatch/image_serializer/image';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        SerializerInterface $serializer
    ){
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
    }

    /*
     * @return bool
     */
    public function getFrameStatus()
    {
        return $this->scopeConfig->getValue(self::FRAMESTATUS, ScopeInterface::SCOPE_STORE);
    }

    /*
     * @return array
     */
    public function getFrameValue() {
        $frameOptions =  $this->scopeConfig->getValue(self::FRAMECONFIG, ScopeInterface::SCOPE_STORE);
        if(!empty($frameOptions)) {
            return $this->serializer->unserialize($frameOptions);
        }
        return null;
    }

    /*
     * @return array
     */
    public function getFrameOptions() {
        $frameOptions = $this->getFrameValue();
        $frames = array();
        if($this->getFrameStatus() && $frameOptions) {
            foreach ($frameOptions as $options) {
                $frames[$options['name']] = $options['image'];
            }
        }

        return $frames;
    }

}
