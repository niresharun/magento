<?php

namespace Ziffity\ContactUs\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Inquire
{


    /**
     * @var Inquires
     */
    protected $inquireSorce;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Inquires $inquireSorce
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $jsonSerializer
     * @param array $data
     */
    public function __construct(
        Inquires $inquireSorce,
        ScopeConfigInterface $scopeConfig,
        Json $jsonSerializer,
        array $data = []
    )
    {
        $this->inquireSorce = $inquireSorce;
        $this->scopeConfig = $scopeConfig;
        $this->jsonSerializer = $jsonSerializer;
    }
    /**
     * Retrieve all attribute options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $inquires = [];
        $data = $this->jsonSerializer->unserialize($this->scopeConfig->getValue('contact_us/inquires/inquires_list', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        if (is_array($data)) {
            foreach ($data as $key => $inquire) {
                $inquires[$key][$inquire['name']] = $inquire;
            }
        }
        return $inquires;
    }
}
