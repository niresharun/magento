<?php

namespace Ziffity\ProductCustomizer\Model;

use Exception;
use \Psr\Log\LoggerInterface;
use Magento\Catalog\Block\Product\View\Gallery;
use Magento\ProductVideo\Block\Product\View\Gallery as ProductVideoGallery;
use Magento\Catalog\Block\Product\View\GalleryOptions;
use Magento\Framework\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Default Config Provider for customframe
 */
class ImageConfigProvider implements ConfigProviderInterface
{

    /**
     * @var Gallery
     */
    protected $galleryBlock;

    /**
     * @var GalleryOptions
     */
    protected $galleryOptions;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $customizerConfig = null;

    /**
     * @param LoggerInterface $logger
     * @param Gallery $galleryBlock
     * @param GalleryOptions $galleryOptions
     * @param ProductVideoGallery $productVideoGallery
     * @param Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param Json $jsonSerializer
     */
    public function __construct(
        LoggerInterface $logger,
        Gallery $galleryBlock,
        GalleryOptions $galleryOptions,
        private ProductVideoGallery $productVideoGallery,
        private Registry $registry,
        private ProductRepositoryInterface $productRepository,
        private Json $jsonSerializer

    ) {
        $this->logger = $logger;
        $this->galleryBlock = $galleryBlock;
        $this->galleryOptions = $galleryOptions;
    }

    /**
     * @param $config
     * @return void
     */
    public function setConfig($config)
    {
        $this->customizerConfig = $config;
    }

    /**
     * Return configuration array
     * @return array|mixed
     */
    public function getConfig()
    {
        return [
            'gallery_data'=>$this->galleryBlock->getGalleryImagesJson(),
            'magnifier'=>$this->galleryBlock->getMagnifier(),
            'gallery_options'=>$this->galleryOptions->getOptionsJson(),
            'fullscreen_options'=>$this->galleryOptions->getFSOptionsJson(),
            'breakpoints'=>$this->galleryBlock->getBreakpoints(),
            'videoGalleryScript'=>$this->buildVideoGalleryScript()
        ];
    }

    public function buildGalleryScript($option)
    {
        return '<script type="text/x-magento-init">
        {
        "[data-gallery-role=gallery-placeholder]": {
            "mage/gallery/gallery": {
                "mixins":["Ziffity_ProductCustomizer/js/magnify"],
                "magnifierOpts": '.$option["magnifier"].',
                "data": '.$option["gallery_data"].',
                "options": '.$option["gallery_options"].',
                "fullscreen": '.$option["fullscreen_options"].',
                 "breakpoints": '.$option["breakpoints"].'
            }
        }
    }
    </script>';
    }

    public function includeCanvasImage($data,$canvas)
    {

        if ($canvas == "false") {
            return $data;
        }
        $index = 1;
        $result = [];
        $canvasImage = [];
        $canvasImage['img'] = $canvas;
        $canvasImage['thumb'] = $canvas;
        $canvasImage['full'] = $canvas;
        $canvasImage['position'] = '1';
        $canvasImage['isMain'] = true;
        $canvasImage['type'] = 'image';
        foreach (json_decode($data,true) as $key=>$value)
        {
            $index++;
            $value['isMain'] = false;
            $value['position'] = $index;
            $result[$key] = $value;
        }
        array_unshift($result,$canvasImage);
        return json_encode($result);
    }

    public function buildVideoGalleryScript($postData = null)
    {
        if (empty($this->registry->registry('product')) && !empty($postData['sku'])) {
            $product =  $this->productRepository->get($postData['sku']);
            $this->registry->register('product', $product);
        }

        $videoData = $this->productVideoGallery->getMediaGalleryDataJson();
        $videoSettingsJson = $this->productVideoGallery->getVideoSettingsJson();
        $videoSettingsJson = ($videoSettingsJson !== null) ? $videoSettingsJson : "[]";
        $optionsMediaGallery = $this->productVideoGallery->getOptionsMediaGalleryDataJson();
        $optionsMediaGallery = ($optionsMediaGallery !== null) ? $optionsMediaGallery : "[]";

        if ($videoData !== null && (isset($postData['canvas']) && $postData['canvas'] != 'false')) {
            $videoData = $this->jsonSerializer->unserialize($videoData);
            array_unshift($videoData, [
                'mediaType' => "image",
                'videoUrl' => null,
                'isBase' => false
            ]);
            $videoData = $this->jsonSerializer->serialize($videoData);
        } else if ($videoData == null) {
            $videoData = "[]";
        }

        return '<script type="text/x-magento-init">
        {
        "[data-gallery-role=gallery-placeholder]": {
            "Magento_ProductVideo/js/fotorama-add-video-events": {
                "videoData": '.$videoData.',
                "videoSettings": '.$videoSettingsJson.',
                "optionsVideoData": '.$optionsMediaGallery.'
            }
        }
        }
        </script>';
    }
}
