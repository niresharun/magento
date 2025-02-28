<?php

namespace Ziffity\CustomFrame\Setup\Patch\Data;

use Exception;
use Magento\Config\Model\Config\Factory as Config;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\Serializer\Json;

class InstallAttributeValuesInConfig implements DataPatchInterface
{

    /**
     * @var File
     */
    public $file;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var Config
     */
    protected $config;
    /**
     * @var Reader
     */
    protected $moduleDirReader;

    /**
     * @param Config $config
     * @param Reader $moduleDirReader
     * @param File $file
     * @param LoggerInterface $logger
     * @param Json $serializer
     */
    public function __construct(
        Config $config,
        Reader $moduleDirReader,
        File $file,
        LoggerInterface $logger,
        Json $serializer
    )
    {
        $this->config = $config;
        $this->moduleDirReader = $moduleDirReader;
        $this->file = $file;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return void
     */
    public function apply()
    {
        try {
            $data = $this->getFileData();
            $data = $this->processData($data);
            $path = 'custom_frame/attribute_values/fractional';
            $configModel = $this->config->create();
            $configModel->setDataByPath($path, $data);
            $configModel->save();
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }

    /**
     * This function installs the attribute values in admin config as in M1.
     *
     * @return mixed
     */
    public function getFileData()
    {
        $filePath = $this->moduleDirReader
                ->getModuleDir('etc', 'Ziffity_CustomFrame')
            . '/serializedData.json';
        $fileContents = $this->file->read($filePath);
        return $this->serializer->unserialize($fileContents);
    }

    /**
     * This function processes the data in array with key as value.
     *
     * @param array $data
     * @return false|string
     */
    public function processData($data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = ['value' => $value['fractional_value']];
        }
        return json_encode($result, JSON_FORCE_OBJECT);
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
