<?php

namespace Ziffity\SavedDesigns\ViewModel;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\Store;
use Magento\Theme\Block\Html\Header\Logo;
use Magento\Framework\Registry;
use Ziffity\CustomFrame\Model\Product\Price;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;

class ProvidePdfData implements ArgumentInterface
{

    /**
     * @var ProductRepositoryInterface
     */
    protected $product;

    /**
     * @var Price
     */
    protected $price;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Logo
     */
    protected $logo;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @param Logo $logo
     * @param Repository $assetRepo
     * @param Emulation $appEmulation
     * @param State $appState
     * @param Registry $registry
     * @param Price $price
     * @param ProductRepositoryInterface $product
     * @param RequestInterface $request
     */
    public function __construct(
    Logo $logo,
    Repository $assetRepo,
    Emulation $appEmulation,
    State $appState,
    Registry $registry,
    Price $price,
    ProductRepositoryInterface $product
    )
    {
        $this->logo = $logo;
        $this->assetRepo = $assetRepo;
        $this->appEmulation = $appEmulation;
        $this->appState = $appState;
        $this->registry = $registry;
        $this->price = $price;
        $this->product = $product;
    }

    /**
     * This function gets the logo file in base 64 encoded format.
     *
     * @param bool $useLogoUrl
     * @return string
     * @throws LocalizedException
     */
    public function getLogo(bool $useLogoUrl = true): string
    {
        if ($useLogoUrl) {
            $logoSrc = $this->getEmulatedResult($this->logo, 'getLogoSrc');
            if ($logoSrc) {
                return $logoSrc;
            }
        }

        $asset = $this->assetRepo->createAsset('images/logo.svg', ['area' => 'frontend']);
        return 'data:image/' . $asset->getContentType() . ';base64,' . base64_encode($asset->getContent());
    }

    /**
     * This function to get the logo file starts stops the environment.
     *
     * @param mixed $object
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    private function getEmulatedResult($object, string $method, array $params = [])
    {
        $this->appEmulation->startEnvironmentEmulation(Store::DEFAULT_STORE_ID);
        $url = $this->appState->emulateAreaCode(
            Area::AREA_FRONTEND,
            [$object, $method],
            $params
        );
        $this->appEmulation->stopEnvironmentEmulation();

        return $url;
    }

    /**
     * This function processes the additional data to be formatted returned array.
     *
     * @return array|float
     * @throws NoSuchEntityException
     */
    public function getAdditionalData()
    {
        $data = $this->registry->registry('share_additional_data');
        if (isset($data['product_id']) && isset($data['additional_data'])){
            $data['additional_data'] = json_decode($data['additional_data'],true);
            $product = $this->product->getById($data['product_id']);
            $priceSummary = $this->price->getPriceSummary($product,$data['additional_data']);
            if (isset($priceSummary['price_summary'])) {
                $priceSummary['price_summary'] = $this->formatPriceSummary($priceSummary);
                $priceSummary['your_selections'] = $data['your_selections'];
                return $priceSummary;
            }
        }
        return [];
    }

    /**
     * This function prepares the breakup from the raw data to a array format.
     *
     * @param array $priceSummary
     * @return array
     */
    public function formatPriceSummary($priceSummary)
    {
        $result  = [];
        foreach ($priceSummary['price_summary'] as $key=>$value)
        {
            switch ($key) {
                case 'frame':
                    $result[$key] = ['label'=>'Frame','value'=>$value];
                    break;
                case 'addons':
                    $result[$key] = ['label'=>'Add-on','value'=>$value];
                    break;
                case 'mat':
                    $result[$key] = ['label'=>'Mat','value'=>$value];
                    break;
                case 'cork_board':
                    $result[$key] = ['label'=>'Cork Board','value'=>$value];
                    break;
                case 'letter_board':
                    $result[$key] = ['label'=>'Letter Board','value'=>$value];
                    break;
                case 'dryerase_board':
                    $result[$key] = ['label'=>'Dry Erase Board','value'=>$value];
                    break;
                case 'chalk_board':
                    $result[$key] = ['label'=>'Chalk Board','value'=>$value];
                    break;
                case 'glass':
                    $result[$key] = ['label'=>'Glass/Glazing','value'=>$value];
                    break;
                case 'post_finish':
                    $result[$key] = ['label'=>'Post Finish','value'=>$value];
                    break;
                case 'fabric':
                    $result[$key] = ['label'=>'Fabric','value'=>$value];
                    break;
                case 'lighting':
                    $result[$key] = ['label'=>'Lighting','value'=>$value];
                    break;
                case 'laminate_finish':
                    $result[$key] = ['label'=>'Laminate Finish','value'=>$value];
                    break;
                case 'backing_board':
                    $result[$key] = ['label'=>'Backing Board','value'=>$value];
                    break;
                case 'other_components':
                    $result[$key] = ['label'=>'Frame Components & Parts','value'=>$value];
                    break;
                case 'shelves':
                    $result[$key] = ['label'=>'Shelves','value'=>$value];
                    break;
                case 'label':
                    $result[$key] = ['label'=>'Text & Image Labels','value'=>$value];
                    break;
                case 'accessories':
                    $result[$key] = ['label'=>'Accessories','value'=>$value];
                    break;
            }
        }
        return $result;
    }

}
