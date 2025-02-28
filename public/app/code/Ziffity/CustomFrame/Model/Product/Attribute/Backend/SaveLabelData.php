<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Backend;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Ziffity\CustomFrame\Helper\Data as Helper;

class SaveLabelData extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
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
        $canSaveLabel = $this->helper->checkPrimaryProductsOptions($object, 'Labels');
        if ($object->getLabelData() && $canSaveLabel) {
            $labelsTextAndImages = $object->getLabelData();
            if (is_string($labelsTextAndImages)) {
                $labelsTextAndImages = json_decode($labelsTextAndImages, true);
            }
            if (isset($labelsTextAndImages['customHeaders'][0])) {
                $labelsData = $labelsTextAndImages['customHeaders'][0];
                unset($labelsTextAndImages['customHeaders']);
            }
            if (!empty($labelsTextAndImages) && isset($labelsData)) {
                $labelsTextAndImages = array_merge_recursive($labelsData, $labelsTextAndImages);
            }
            $labelsData = $this->prepareLabelsData($labelsTextAndImages);
            empty($labelsData) ? $object->setData($attributeCode, null) :
                $object->setData($attributeCode, json_encode($labelsData));
        }
        if (!$canSaveLabel) {
            $this->deleteImages($object, $attributeCode);
            $this->setNullValue($object, $attributeCode);
        }
        return $this;
    }

    /**
     * This function sets the null value to label data attribute.
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
     * This function is used to delete the images if the label data is going to be deleted.
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
                $oldLabelData = $customAttribute->getValue();
                $oldLabelData = json_decode($oldLabelData, true);
                if (isset($oldLabelData['images'])) {
                    foreach ($oldLabelData['images'] as $data) {
                        $location = $this->helper->directory->getPath('media') . '/catalog/product/labels/';
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
     * @param array $data Labels data.
     *
     * @return string|array
     */
    public function prepareLabelsData($data)
    {
        $helper = $this->helper;
        $labelsData = [];
        if (!empty($data)) {
            $labelsData = [
                'position'  => $data['position'],
                'size'      => [
                    'height' => $helper->formatFloatToFractional($data['size']['height']),
                    'width'  => $helper->formatFloatToFractional($data['size']['width']),
                ],
                'font_conf' => [
                    'size_min_inch'  => $helper->formatFloatToFractional($data['font_size']['min']),
                    'size_step_inch' => $helper->formatFloatToFractional($data['font_size']['step']),
                    'size_def_inch'  => $helper->formatFloatToFractional($data['font_size']['default']),
                ],
            ];
            if (!empty($data['images'])) {
                foreach ($data['images'] as &$image) {
                    if (strpos($image['url'], 'base64') !== false) {
                        $imagePath = $this->helper
                            ->saveBase64Image($image['url'], 'labels', '', 'product_labels');
                        $image['url'] = $imagePath;
                    }
                    // @codingStandardsIgnoreStart
                    if (strpos($image['url'], 'http') === 0) {
                        // @codingStandardsIgnoreEnd
                        try {
                            $image['url'] = explode('labels/', $image['url'])[1];
                        } catch (\Exception $exception) {
                            continue;
                        }
                    }
                }
                $labelsData['images'] = $data['images'];
            }
            if (!empty($data['texts'])) {
                $labelsData['texts'] = $data['texts'];
            }
            if (!empty($data['bg_color_active'])) {
                $labelsData['bg_color_active'] = $data['bg_color_active'];
            }
        }
        return $labelsData;
    }
}
