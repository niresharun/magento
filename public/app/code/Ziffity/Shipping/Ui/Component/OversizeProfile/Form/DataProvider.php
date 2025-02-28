<?php

namespace Ziffity\Shipping\Ui\Component\OversizeProfile\Form;

use Ziffity\Shipping\Model\OversizeProfileCharge\ResourceModel\OversizeProfileCharge\CollectionFactory as ProfileChargeCollection;
use Ziffity\Shipping\Model\OversizeProfile\ResourceModel\OversizeProfile\CollectionFactory;
use Ziffity\Shipping\Model\OversizeProfileCharge\ProfileCharge as Model;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Framework\App\RequestInterface;
use Ziffity\Shipping\Helper\Data;

class DataProvider extends ModifierPoolDataProvider
{

    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * @var array
     */
    protected $loadedData = [];

    /**
     * @var ProfileChargeCollection
     */
    protected ProfileChargeCollection $secondaryCollection;

    /**
     * @var Model
     */
    protected $collection;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RequestInterface $request
     * @param CollectionFactory $collection
     * @param ProfileChargeCollection $secondaryCollection
     * @param Data $helper
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        CollectionFactory $collection,
        ProfileChargeCollection $secondaryCollection,
        Data $helper,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->request = $request;
        $this->collection = $collection->create();
        $this->helper = $helper;
        $this->secondaryCollection = $secondaryCollection;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $requestId = $this->request->getParam('profile_id');
        if ($requestId !== null) {
            $this->loadedData = $this->helper
                ->buildShippingProfile(
                    $requestId,
                    $this->collection,
                    $this->loadedData
                );
            $this->loadedData = $this->helper
                ->buildDynamicRows(
                    $requestId,
                    $this->secondaryCollection,
                    $this->loadedData
                );
        }
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        return $this->loadedData;
    }
}
