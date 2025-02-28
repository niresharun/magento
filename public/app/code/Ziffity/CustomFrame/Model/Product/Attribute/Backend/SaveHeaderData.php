<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Backend;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Ziffity\CustomFrame\Helper\Data as Helper;

class SaveHeaderData extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{

    /**
     * @var Helper
     */
    public $helper;

    /**
     * @param Helper $helper
     */
    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Before Attribute Save Process
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $canSaveHeader = $this->helper->checkPrimaryProductsOptions($object, 'Headers');
        //TODO:For demo purpose commenting this line since header and label data cannot be saved in 1 product.
        if ($object->getHeaderData() && $canSaveHeader) {
            $headersTextAndImages = $object->getHeaderData();
            if (is_string($headersTextAndImages)) {
                $headersTextAndImages = json_decode($headersTextAndImages, true);
            }
            if (isset($headersTextAndImages['customHeaders'][0])) {
                $headersData = $headersTextAndImages['customHeaders'][0];
                unset($headersTextAndImages['customHeaders']);
            }
            if (!empty($headersTextAndImages) && isset($headersData)) {
                $headersTextAndImages = array_merge_recursive($headersData, $headersTextAndImages);
            }
            $headersData = $this->prepareHeadersData($headersTextAndImages);
            $object->setData($attributeCode, json_encode($headersData));
        }
        if (!$canSaveHeader) {
            $this->deleteImages($object, $attributeCode);
            $this->setNullValue($object, $attributeCode);
        }
        return $this;
    }

    /**
     * This function sets the null value to opening size attribute.
     *
     * @param object $object
     * @param string $attributeCode
     * @return void
     */
    public function setNullValue($object, $attributeCode)
    {
        $object->setData($attributeCode, null);
    }

    /**
     * This function is used to delete the images if the header data is going to be deleted.
     *
     * @param object $object
     * @param string $attributeCode
     * @return void
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function deleteImages($object, $attributeCode)
    {
        if ($object->getEntityId()) {
            $product = $this->helper->productRepository->getById($object->getEntityId());
            $customAttribute = $product->getCustomAttribute($attributeCode);
            $oldOpeningData = $customAttribute ? $customAttribute->getValue(): null;
            $oldOpeningData = $oldOpeningData ? json_decode($oldOpeningData, true): null;
            if ($customAttribute && $oldOpeningData) {
                $oldHeaderData = $customAttribute->getValue();
                $oldHeaderData = json_decode($oldHeaderData, true);
                if (isset($oldHeaderData['images'])) {
                    foreach ($oldHeaderData['images'] as $data) {
                        $location = $this->helper->directory->getPath('media') . '/catalog/product/headers/';
                        if (isset($data['url']) && strpos($data['url'], 'http') !== 0) {
                            try {
                                $this->helper->file->rm($location . $data['url']);
                            } catch (\Exception $exception) {
                                continue;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Prepare headers data for save.
     *
     * @param array $data
     *
     * @return string|array
     */
    public function prepareHeadersData($data)
    {
        $headerData = [];
        if (!empty($data)) {
            $headerData = [
                'position'  => $data['position'],
                'size'      => [
                    'height' => $this->helper->formatFloatToFractional($data['size']['height']),
                    'width'  => $this->helper->formatFloatToFractional($data['size']['width']),
                ],
                'font_conf' => [
                    'size_min_inch'  => $this->helper->formatFloatToFractional($data['font_size']['min']),
                    'size_step_inch' => $this->helper->formatFloatToFractional($data['font_size']['step']),
                    'size_def_inch'  => $this->helper->formatFloatToFractional($data['font_size']['default']),
                ],
            ];
            if (!empty($data['images'])) {
                foreach ($data['images'] as &$image) {
                    if (strpos($image['url'], 'base64') !== false) {
                        $imagePath = $this->helper
                            ->saveBase64Image($image['url'], 'headers', '', 'product_headers');
                        $image['url'] = $imagePath;
                    }
                    // @codingStandardsIgnoreStart
                    if (strpos($image['url'], 'http') === 0) {
                        // @codingStandardsIgnoreEnd
                        try {
                            $image['url'] = explode('headers/', $image['url'])[1];
                        } catch (\Exception $exception) {
                            continue;
                        }
                    }
                }
                $headerData['images'] = $data['images'];
            }
            if (!empty($data['texts'])) {
                $headerData['texts'] = $data['texts'];
            }
            if (!empty($data['bg_color_active'])) {
                $headerData['bg_color_active'] = $data['bg_color_active'];
            }
        }
        return $headerData;
    }
}
