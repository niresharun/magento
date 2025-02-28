<?php

namespace Ziffity\ProductCustomizer\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Framework\Serialize\Serializer\Json;
use Ziffity\ProductCustomizer\Helper\Selections;
use Ziffity\SavedDesigns\ViewModel\ProcessSaveDesigns;

class DefaultItem
{
    protected $serializer;

    protected $selectionsHelper;

    protected $productRepository;

    protected $processSavedDesigns;

    public function __construct(
        Selections $selectionsHelper,
        ProductRepositoryInterface $productRepository,
        ProcessSaveDesigns $processSavedDesigns,
        Json $serializer = null
    )
    {
        $this->selectionsHelper = $selectionsHelper;
        $this->productRepository = $productRepository;
        $this->processSavedDesigns = $processSavedDesigns;
        $this->serializer = $serializer;
    }

    public function aroundGetItemData($subject, \Closure $proceed, Item $item)
    {
        $data = $proceed($item);
        $atts = [];
        $savedImg = null;
        if($item->getAdditionalData()) {
            $additionalData = $item->getAdditionalData();
            $selectionsHelper = $this->selectionsHelper;
            $steps = $selectionsHelper->getCompletedStepsFromOptions($item->getAdditionalData());
            $addData =  $selectionsHelper->getUnserializedData($item->getAdditionalData());
            $product = $this->productRepository->getById($item->getProductId());
            $additionalData = $selectionsHelper->getSelections($addData, $product, $steps );
            if (
                !empty($addData['additional_data']['canvasData']) &&
                $this->processSavedDesigns->checkImageExists($addData['additional_data']['canvasData'])
            ) {
                $savedImg = $this->processSavedDesigns->getImagePath($addData['additional_data']['canvasData']);
            }
            $atts = [
                "additional_data" => $additionalData,
                "saved_img"  => $savedImg
            ];
        }
        return array_merge($data, $atts);
    }
}
