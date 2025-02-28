<?php
declare(strict_types=1);

namespace Ziffity\ProductStamp\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;

class ImageConfig
{
    const XML_PATH_IMAGE_SERIALIZER = 'swatch/image_serializer/';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SerializerInterface $serializer,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
    }

    /**
     * @return array
     */
    public function getSwatches(): array
    {
        $data = $this->scopeConfig->getValue(self::XML_PATH_IMAGE_SERIALIZER . 'image');
        if (!$data) {
            return [];
        }
        return $this->serializer->unserialize($data);
    }
}
