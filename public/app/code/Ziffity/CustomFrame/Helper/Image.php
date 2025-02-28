<?php

namespace Ziffity\CustomFrame\Helper;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Image extends Labels
{

    /**
     * Base directory
     *
     * @var string
     */
    protected $baseDir = 'media' . DIRECTORY_SEPARATOR .
    'catalog' . DIRECTORY_SEPARATOR . 'product' . DIRECTORY_SEPARATOR;

    // @codingStandardsIgnoreStart
    /**
     * Save base64 image.
     *
     * @param string $image
     * @param string $filePath
     * @param string $filename
     * @param string $prefix
     * @return mixed|string
     * @throws \Exception
     */
    public function saveBase64Image($image, $filePath, $filename = '', $prefix = '')
    {
        $imgData = explode(',', $image);
        $isImage = $this->validateBase64Image($imgData[1]);
        if (!$isImage) {
            throw new LocalizedException(
                __('Image data has wrong format.')
            );
        }
        $imgData = base64_decode($imgData[1]);
        if (!$filename) {
            $filename = sprintf('%s.%s', uniqid($prefix . '_'), 'png');
        }
        $this->file->setAllowCreateFolders(true);
        $path = $this->getBaseDir($filePath);
        $this->file->open(['path' => $path]);
        $filePath = $path . DIRECTORY_SEPARATOR . $filename;
        $fileCreated = true;
        try {
            $image = imagecreatefromstring($imgData);
            imagecolortransparent($image, imagecolorallocatealpha($image, 0, 0, 0, 127));
            imagealphablending($image, false);
            imagesavealpha($image, true);
            imagepng($image, $filePath);
        } catch (\Exception $e) {
            $this->file->rm($filePath);
            $fileCreated = false;
        }
        if (!$fileCreated) {
            throw new LocalizedException(
                __('Image file not saved.')
            );
        }
        return $filename;
    }

    /**
     * This function checks if the base 64 string is valid or not.
     *
     * @param string $base64
     * @return bool
     * @throws FileSystemException
     */
    public function validateBase64Image($base64)
    {
        try {
            $image = imagecreatefromstring(base64_decode($base64));
        } catch (\Exception $e) {
            return false;
        }
        if (!$image) {
            return false;
        }
        imagepng($image, $this->directory->getPath('var') . DIRECTORY_SEPARATOR . 'tmp64check.png');
        $info = getimagesize($this->directory->getPath('var') . DIRECTORY_SEPARATOR . 'tmp64check.png');
        $this->file->rm($this->directory->getPath('var') . DIRECTORY_SEPARATOR . 'tmp64check.png');
        if ($info[0] > 0 && $info[1] > 0 && $info['mime']) {
            return true;
        }
        return false;
    }
    // @codingStandardsIgnoreEnd

    /**
     * Retrieve Base Dir.
     *
     * @param string $path
     * @return string
     */
    public function getBaseDir($path)
    {
        return $this->baseDir.$path;
    }
}
