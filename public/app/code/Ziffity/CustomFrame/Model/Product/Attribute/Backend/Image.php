<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Backend;

use Magento\Framework\File\Uploader;
use Ziffity\CustomFrame\Helper\Image as HelperImage;
use Magento\Framework\File\UploaderFactory;
use Psr\Log\LoggerInterface;

class Image extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var HelperImage
     */
    protected $helperImage;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @param HelperImage $helperImage
     * @param UploaderFactory $uploaderFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        HelperImage $helperImage,
        UploaderFactory $uploaderFactory,
        LoggerInterface $logger)
    {
        $this->helperImage = $helperImage;
        $this->uploaderFactory = $uploaderFactory;
        $this->logger = $logger;
    }

    /**
     * Save uploaded file and set its name to category
     *
     * @param \Magento\Framework\DataObject $object
     *
     * @return void
     */
    public function afterSave($object)
    {
        $value = $object->getData($this->getAttribute()->getName());
        if (is_array($value) && !empty($value['delete'])) {
            $object->setData($this->getAttribute()->getName(), '');
            $this->getAttribute()->getEntity()
                ->saveAttribute($object, $this->getAttribute()->getName());
            return;
        }
        $catalogPath = str_replace('_', DS, $this->getAttribute()->getAttributeCode());
        $path = $this->helperImage->getBaseDir('media') . DS . 'catalog' . DS . 'product' . DS . $catalogPath;
        try {
            $uploader = $this->uploaderFactory->create();
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($path);
            $object->setData($this->getAttribute()->getName(), $catalogPath . DS . $result['file']);
            $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
        } catch (\Exception $e) {
            if ($e->getCode() !== Uploader::TMP_NAME_EMPTY) {
                $this->logger->debug($e->getMessage());
            }
            return;
        }
    }
}
