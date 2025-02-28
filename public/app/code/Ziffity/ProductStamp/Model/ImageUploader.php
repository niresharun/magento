<?php
declare(strict_types=1);

namespace Ziffity\ProductStamp\Model;

use Magento\MediaStorage\Model\File\Uploader;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;


class ImageUploader
{
    const PRODUCT_STAMP_PATH = '/ProductStamp/image_serializer';
    /**
     * @var \Ziffity\ProductStamp\Model\ArrayFileModifier
     */
    private $arrayFileModifier;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var string
     */
    private $uploadDir;

    /**
     * @var array
     */
    private $allowExtensions;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var File
     */
    protected $fileDriver;

    /**
     * @param ArrayFileModifier $arrayFileModifier
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param string $uploadDir
     * @param array $allowExtensions
     * @param DirectoryList $directoryList
     * @param File $fileDriver
     */
    public function __construct(
        \Ziffity\ProductStamp\Model\ArrayFileModifier $arrayFileModifier,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        string $uploadDir,
        array $allowExtensions,
        DirectoryList $directoryList,
        File $fileDriver
    ) {
        $this->arrayFileModifier = $arrayFileModifier;
        $this->uploaderFactory = $uploaderFactory;
        $this->uploadDir = $uploadDir;
        $this->allowExtensions = $allowExtensions;
        $this->directoryList = $directoryList;
        $this->fileDriver = $fileDriver;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function upload(): array
    {
        $result = [];
        $files = $this->arrayFileModifier->modify();
        if (!$files) {
            return $result;
        }

        foreach ($files as $id => $file) {
            try {
                $uploader = $this->uploaderFactory->create(['fileId' => $id]);
                $uploader->setAllowedExtensions($this->allowExtensions);
                $uploader->setAllowRenameFiles(true);
                $uploader->addValidateCallback('size', $this, 'validateMaxSize');
                $newFileName = $this->getNewFileName($uploader);
                $this->checkAndCreateFolder();
                $uploader->save($this->uploadDir, $newFileName);
                $result[$id] = $this->getFullFilPath($newFileName);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('%1', $e->getMessage()));
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function checkAndCreateFolder()
    {
        $path = $this->directoryList->getPath('media') . self::PRODUCT_STAMP_PATH;

        if (!$this->fileDriver->isDirectory($path)) {
            $this->fileDriver->createDirectory($path);
        }

        return $path;
    }

    /**
     * @param Uploader $uploader
     * @return string
     */
    private function getNewFileName(Uploader $uploader): string
    {
        return sprintf(
            '%s.%s',
            uniqid(),
            $uploader->getFileExtension()
        );
    }

    /**
     * @param string $filename
     * @return string
     */
    private function getFullFilPath(string $filename): string
    {
        return sprintf(
            '/%s/%s',
            self::PRODUCT_STAMP_PATH,
            $filename
        );
    }
}
