<?php


namespace Ziffity\ProductCustomizer\Cron;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File as FileWriter;
use Magento\Framework\Filesystem\Io\File;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as QuoteItemCollection;
use Ziffity\SavedDesigns\Model\ResourceModel\SavedDesigns\CollectionFactory as SavedDesignsCollection;
use Psr\Log\LoggerInterface;

class DeleteGarbageCanvasImages
{

    protected $savedDesignsCollection;

    /**
     * @var QuoteItemCollection
     */
    protected $quoteItemCollection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var FileWriter
     */
    protected $driverFile;

    /**
     * @var File
     */
    protected $file;

    /**
     * @param LoggerInterface $logger
     * @param DirectoryList $directoryList
     * @param FileWriter $driverFile
     * @param QuoteItemCollection $quoteItemCollection
     */
    public function __construct(
        LoggerInterface     $logger,
        DirectoryList       $directoryList,
        FileWriter          $driverFile,
        QuoteItemCollection $quoteItemCollection,
        SavedDesignsCollection $savedDesignsCollection
    )
    {
        $this->logger = $logger;
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->quoteItemCollection = $quoteItemCollection;
        $this->savedDesignsCollection = $savedDesignsCollection;
    }

    /**
     * Cronjob to clean the garbage images from quote_item and saved tables.
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            $canvasFiles = $this->getCanvasFiles();
            $canvasFiles = $this->findInQuoteItem($canvasFiles);
            $canvasFiles = $this->findInSavedDesigns($canvasFiles);
            //TODO: Have to check if the image file is present in the order tables for order emails
//            $this->deleteCanvasFiles($canvasFiles);
        }catch (\Exception $exception){
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * This function finds the canvas image files in the ziffity saved designs table.
     *
     * @param array $canvasFiles
     * @return array
     */
    public function findInSavedDesigns($canvasFiles)
    {
        $filesToBeRemoved = [];
        foreach ($canvasFiles as $canvasFile) {
            $collection = $this->savedDesignsCollection->create();
            $collection->addFieldToFilter('image_url', ['like' => '%' . $canvasFile['filename'] . '%']);
            if (empty($collection->getItems())) {
                $filesToBeRemoved[] = $canvasFile;
            }
        }
        return $filesToBeRemoved;
    }

    /**
     * This function gets the canvas images from the directory and processes it
     * an array with path and filename for finding it in the DB.
     *
     * @return array
     */
    public function getCanvasFiles()
    {
        $fileNames = [];
        try {
            $path = $this->directoryList->getPath('media') . \Ziffity\SavedDesigns\Model\SavedDesigns::IMAGE_PATH;
            $paths = $this->driverFile->readDirectory($path);
            foreach ($paths as $key => $path) {
                $file = explode('/', $path);
                $fileNames[] = ['filename' => end($file), 'path' => $paths[$key]];
            }
        } catch (FileSystemException $e) {
            $this->logger->error($e->getMessage());
            return [];
        }
        return $fileNames;
    }

    /**
     * This function find the canvas image in quote_item table , if not found then
     * it is pushed in an array for further checking and deletion.
     *
     * @param array $canvasFiles
     * @return array
     */
    public function findInQuoteItem($canvasFiles)
    {
        $filesToBeRemoved = [];
        foreach ($canvasFiles as $canvasFile) {
            $collection = $this->quoteItemCollection->create();
            $collection->addFieldToFilter('additional_data', ['like' => '%' . $canvasFile['filename'] . '%']);
            if (empty($collection->getItems())) {
                $filesToBeRemoved[] = $canvasFile;
            }
        }
        return $filesToBeRemoved;
    }

    /**
     * This function deletes the canvas images using the file paths.
     *
     * @param array $canvasFiles
     * @return void
     */
    public function deleteCanvasFiles($canvasFiles)
    {
        try {
            foreach ($canvasFiles as $canvasFile) {
                $this->driverFile->deleteFile($canvasFile['path']);
            }
        } catch (FileSystemException $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
