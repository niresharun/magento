<?php
declare(strict_types=1);

namespace Ziffity\ProductStamp\Model\Config\Backend\Serialized;

use Magento\Framework\Serialize\Serializer\Json;
use Ziffity\ProductStamp\Block\Adminhtml\System\Config\ImageFields;

class ArraySerialized extends \Magento\Config\Model\Config\Backend\Serialized\ArraySerialized
{
    /**
     * @var \Ziffity\ProductStamp\Model\ImageUploaderFactory
     */
    private $imageUploaderFactory;

    /**
     * @var \Ziffity\ProductStamp\Model\Config\ImageConfig
     */
    private $imageConfig;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Ziffity\ProductStamp\Model\Config\ImageConfig $imageConfig
     * @param \Ziffity\ProductStamp\Model\ImageUploaderFactory $imageUploaderFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Ziffity\ProductStamp\Model\Config\ImageConfig $imageConfig,
        \Ziffity\ProductStamp\Model\ImageUploaderFactory $imageUploaderFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        Json $serializer = null
    ) {
        $this->imageUploaderFactory = $imageUploaderFactory;
        $this->imageConfig = $imageConfig;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data,
            $serializer
        );
    }

    /**
     * @return ArraySerialized
     */
    public function beforeSave(): ArraySerialized
    {
        $value = $this->getValue();
        $value = $this->mapRows($value);
        $this->setValue($value);
        return parent::beforeSave();
    }

    /**
     * @param array $rows
     * @return array
     */
    private function mapRows(array $rows): array
    {
        $iconUploader = $this->imageUploaderFactory->create([
            'path' => $this->getPath(),
            'uploadDir' => $this->getUploadDir(),
        ]);
        $uploadedFiles = $iconUploader->upload();
        $swatches = $this->imageConfig->getSwatches();
        foreach ($rows as $id => $data) {
            if (isset($uploadedFiles[$id])) {
                $rows[$id][ImageFields::IMAGE_FIELD] = $uploadedFiles[$id];
                continue;
            }
            if (!isset($swatches[$id])) {
                unset($swatches[$id]);
            } else {
                $rows[$id] = $this->matchRow($data, $swatches[$id]);
            }
        }
        return $rows;
    }

    /**
     * @param array $row
     * @param array $configTabIcon
     * @return array
     */
    private function matchRow(array $row, array $configTabIcon): array
    {
        foreach ($row as $fieldName => $value) {
            if (is_array($value) && $fieldName == ImageFields::IMAGE_FIELD) {
                $row[ImageFields::IMAGE_FIELD] = $configTabIcon[ImageFields::IMAGE_FIELD];
            }
        }
        return $row;
    }

    /**
     * @return string
     */
    private function getUploadDir(): string
    {
        $fieldConfig = $this->getFieldConfig();

        if (!array_key_exists('upload_dir', $fieldConfig)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The base directory to upload file is not specified.')
            );
        }

        if (is_array($fieldConfig['upload_dir'])) {
            $uploadDir = $fieldConfig['upload_dir']['value'];
            if (array_key_exists('scope_info', $fieldConfig['upload_dir'])
                && $fieldConfig['upload_dir']['scope_info']
            ) {
                $uploadDir = $this->_appendScopeInfo($uploadDir);
            }

            if (array_key_exists('config', $fieldConfig['upload_dir'])) {
                $uploadDir = $this->getUploadDirPath($uploadDir);
            }
        } else {
            $uploadDir = (string)$fieldConfig['upload_dir'];
        }

        return $uploadDir;
    }
}
