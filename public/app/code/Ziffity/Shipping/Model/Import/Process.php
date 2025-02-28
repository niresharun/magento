<?php

namespace Ziffity\Shipping\Model\Import;

use Magento\Framework\Filesystem;
use Psr\Log\LoggerInterface;

class Process
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Filesystem instance
     *
     * @var \Magento\Framework\Filesystem
     * @since 100.1.0
     */
    protected $filesystem;

    public function __construct(Filesystem $filesystem,LoggerInterface $logger)
    {
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    /**
     * @param string $filePath
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     */
    public function getCsvFile($filePath)
    {
        $pathInfo = pathinfo($filePath);
        $dirName = $pathInfo['dirname'] ?? '';
        $fileName = $pathInfo['basename'] ?? '';
        $directoryRead = $this->filesystem->getDirectoryReadByPath($dirName, Filesystem\DriverPool::FILE);
        return $directoryRead->openFile($fileName);
    }

    /**
     * @param \Magento\Framework\Filesystem\File\ReadInterface $file
     * @return array
     */
    public function processData($file)
    {
        $rowNumber = 1;
        $headers = [];
        $items = [];
        while (false !== ($csvLine = $file->readCsv())) {
            try {
                if ($rowNumber == 1) {
                    foreach ($csvLine as $key=>$value) {
                        $headers[$key] = $value;
                    }
                }
                if ($rowNumber !== 1 && !empty($headers)){
                    $items[$rowNumber] = array_combine($headers,array_values($csvLine));
                }
                if (empty($csvLine)) {
                    continue;
                }
                $rowNumber++;
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
        return $items;
    }
}
