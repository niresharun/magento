<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Backend;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Ziffity\CustomFrame\Helper\Data as Helper;

class SaveOpeningData extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{

    /**
     * @var Helper
     */
    protected $helper;

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
        $canSaveOpening = $this->helper->checkPrimaryProductsOptions($object, ['Openings','Headers','Labels']);
        if ($canSaveOpening) {
            $object = $this->saveImageFromBase64($object);
        }
        if (!$canSaveOpening) {
            $this->deleteImages($attributeCode, $object);
            $this->setNullValue($object, $attributeCode);
        }
        return $this;
    }

    /**
     * This function is used to delete the images if the opening data is going to be deleted.
     *
     * @param string $attributeCode
     * @param object $object
     * @return void
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function deleteImages($attributeCode, $object)
    {
        if ($object->getEntityId()) {
            $product = $this->helper->productRepository->getById($object->getEntityId());
            $customAttribute = $product->getCustomAttribute($attributeCode);
            if($customAttribute) {
                $oldOpeningData = $customAttribute->getValue();
                $oldOpeningData = json_decode($oldOpeningData, true);
                if ($customAttribute && $oldOpeningData) {
                    foreach ($oldOpeningData as $data) {
                        $location = $this->helper->directory->getPath('media') . '/catalog/product/opening/';
                        if (isset($data['img']['converted']) && isset($data['img']['url'])) {
                            try {
                                $this->helper->file->rm($location . $data['img']['url']);
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
     * This function saves the image data of base64 and converts into file.
     *
     * @param object $object
     * @return mixed
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function saveImageFromBase64($object)
    {
        $openingData = $object->getOpeningData();
        $datum = json_decode($openingData, true);
        foreach ($datum as $key => $data) {
            $location = $this->helper->directory->getPath('media') . '/catalog/product/opening/';
            if (isset($data['img']) && !isset($data['img']['converted']) &&
                strpos($data['img']['url'], 'data:image') === 0) {
                if ($this->helper->file->checkAndCreateFolder($location, 0775)) {
                    $image = $data['img']['url'];
                    $image_parts = explode(";base64,", $image);
                    // @codingStandardsIgnoreStart
                    $image_base64 = base64_decode($image_parts[1]);
                    $openingImageId = $key + 1;
                    $filename = "opening_{$openingImageId}_{$object->getSku()}.jpeg";
                    $file = $location . $filename;
                    file_put_contents($file, $image_base64);
                    $datum[$key]['img']['url'] = $filename;
                    $datum[$key]['img']['converted'] = true;
                }
            }
            if (isset($data['img']['url']) && strpos($data['img']['url'], 'http') == 0) {
                // @codingStandardsIgnoreEnd
                try {
                    $datum[$key]['img']['url'] = explode('opening/', $data['img']['url'])[1];
                    $datum[$key]['img']['converted'] = true;
                } catch (\Exception $exception) {
                    continue;
                }
            }
        }
        return $datum == [] ? $object->setOpeningData(null) : $object->setOpeningData(json_encode($datum));
    }
}
