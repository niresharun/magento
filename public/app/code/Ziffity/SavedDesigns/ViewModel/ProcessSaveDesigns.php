<?php
namespace Ziffity\SavedDesigns\ViewModel;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Ziffity\SavedDesigns\Helper\Data as Helper;

class ProcessSaveDesigns implements ArgumentInterface
{

    /**
     * @var Helper
     */
    public $helper;

    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param Helper $helper
     */
    public function __construct(
    	\Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Helper $helper
    ){
	    $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }

    /**
     * This function checks if the image exists in that path or not.
     *
     * @param string|null $image
     * @return bool
     * @throws FileSystemException
     */
    public function checkImageExists($image)
    {
        $fileValidator = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaAbspath = $fileValidator->getAbsolutePath();
        $imageAbsolutePath = $mediaAbspath . \Ziffity\SavedDesigns\Model\SavedDesigns::IMAGE_PATH . $image;
        return $fileValidator->isFile($imageAbsolutePath);
    }

    /**
     * This function gets the file path using the image file name and returns it.
     *
     * @param string|null $image
     * @return string
     * @throws NoSuchEntityException
     */
    public function getImagePath($image) {
        $mediaUrl = $this->storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl . \Ziffity\SavedDesigns\Model\SavedDesigns::IMAGE_PATH . $image;
    }
}
