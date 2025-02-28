<?php
declare(strict_types=1);

namespace Ziffity\ProductCustomizer\Console;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\State;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Catalog\Model\Product\Gallery\CreateHandler;
use Magento\Framework\Serialize\Serializer\Json;

class ProductVideosToGallery extends Command
{
    const YOUTUBE_API_KEY_CONFIG_PATH = 'catalog/product_video/youtube_api_key';

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param State $state
     * @param DirectoryList $dir
     * @param CurlFactory $curlFactory
     * @param CreateHandler $createHandler
     * @param Json $jsonSerializer
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private ProductCollectionFactory $productCollectionFactory,
        private ProductRepositoryInterface $productRepository,
        private State $state,
        private DirectoryList $dir,
        private CurlFactory $curlFactory,
        private CreateHandler $createHandler,
        private Json $jsonSerializer,
        private ScopeConfigInterface $scopeConfig,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('product:videosync');
        $this->setDescription('Sync imported product video attribute data into media gallery');

        parent::configure();
    }

    /**
     * @return float
     */
    public function getConfigYoutubeApiKey()
    {
        return $this->scopeConfig->getValue(
            self::YOUTUBE_API_KEY_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
            $exitCode = 0;
            $youtubeApiKey = $this->getConfigYoutubeApiKey();
            if (empty($youtubeApiKey)) {
                $output->writeln("<error>Please configure Youtube Api key.<error>");
                return $exitCode;
            }
            $productCollection = $this->productCollectionFactory->create();
            $productCollection
                ->addAttributeToSelect(['product_videos', 'media_gallery'])
                ->addFieldtoFilter('type_id', \Ziffity\CustomFrame\Model\Product\Type::TYPE_CODE)
                ->addAttributeToFilter('product_videos', ['notnull' => true]);

            foreach ($productCollection as $product) {
                $product =  $this->productRepository->get($product['sku']);
                $productVideos = $this->jsonSerializer->unserialize($product->getProductVideos());
                $mediaGalleryData = $product->getMediaGallery();
                $position = 0;
                if (!is_array($mediaGalleryData)) {
                    $mediaGalleryData = ['images' => []];
                }
                $isDataModified = false;
                foreach ($productVideos as $videoData) {
                    $videoId = substr($videoData['url'], strrpos($videoData['url'], '/') + 1);
                    if (strpos($videoId, "watch?v=") !== FALSE)  {
                        $videoId = substr($videoId, strrpos($videoId, '=') + 1);
                    }

                    $isAlreadyExsits = false;
                    foreach ($mediaGalleryData['images'] as &$image) {
                        // To avoid mistake creating duplicate while rerunning this command.
                        if (!empty($image['video_url'])) {
                            $existingVideoId = substr($image['video_url'], strrpos($image['video_url'], '/') + 1);
                            if (strpos($existingVideoId, "watch?v=") !== FALSE)  {
                                $existingVideoId = substr($existingVideoId, strrpos($existingVideoId, '=') + 1);
                            }
                            if ($videoId == $existingVideoId) {
                                $isAlreadyExsits = true;
                                continue;
                            }
                        }
                        if (isset($image['position']) && $image['position'] > $position) {
                            $position = $image['position'];
                        }
                    }
                    if ($isAlreadyExsits) {
                       continue;
                    }
                    $position++;

                    if(!$this->getThumbnailImage($videoId, $youtubeApiKey)){
                        $output->writeln("<comment> For the product sku(".$product->getSku().") video id ".$videoId." is missing.<comment>");
                        continue;
                    }

                    $videoData = [
                        'video_id' => $videoId,
                        'video_title' => $videoData['name'],
                        'video_description' => "",
                        'thumbnail' => "",
                        'video_provider' => "youtube",
                        'video_metadata' => null,
                        'video_url' => $videoData['url'],
                        'media_type' => \Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter::MEDIA_TYPE_CODE,
                    ];

                    $isDataModified = true;
                    $mediaGalleryData['images'][] = array_merge([
                        'file' => $videoId.'.jpg',
                        'label' => $videoData['video_title'],
                        'position' => $position,
                        'disabled' => 0
                    ], $videoData);

                }
                if ($isDataModified) {
                    $product->setMediaGallery($mediaGalleryData);
                    $this->createHandler->execute($product);
                    $product->save();
                }
            }
            $output->writeln("<info>Product videos are synced with product's media gallery<info>");
        } catch (\Exception $e) {
            $output->writeln("<error>".$e->getMessage()."<error>");
            $output->writeln("<error>Product sku".$product->getSku()."<error>");
            $output->writeln("<error>".$this->jsonSerializer->serialize($videoData)."<error>");
        }
        return $exitCode;
    }

    /**
     * @return $this
     */
    public function getThumbnailImage($videoId, $youtubeApiKey)
    {
        $curl = $this->curlFactory->create();
        $url = "https://www.googleapis.com/youtube/v3/videos?id=".$videoId."&part=snippet,contentDetails&key=".$youtubeApiKey."&alt=json&callback=&format=json";
        $curl->get($url);
        if ($curl->getBody()) {
            $response = json_decode($curl->getBody(), true);
        }

        if (isset($response['items'][0])) {
            $tmpData = $response['items'][0];
            $tempImage =  $tmpData['snippet']['thumbnails']['high']['url'];
            $img = $this->dir->getPath('media').'/tmp/catalog/product/'.$videoId.'.jpg';
            file_put_contents($img, file_get_contents($tempImage));
            return true;
        }
        return false;
    }
}
