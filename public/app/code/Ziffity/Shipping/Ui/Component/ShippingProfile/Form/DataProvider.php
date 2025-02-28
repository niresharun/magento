<?php

namespace Ziffity\Shipping\Ui\Component\ShippingProfile\Form;

use Ziffity\Shipping\Model\ProfileCharge\ResourceModel\ProfileCharge\CollectionFactory as ProfileChargeCollection;
use Ziffity\Shipping\Model\ShippingProfile\ResourceModel\ShippingProfile\CollectionFactory;
use Ziffity\Shipping\Model\ShippingProfile\ShippingProfile as Model;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;
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
    protected $secondaryCollection;

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
        $this->secondaryCollection = $secondaryCollection;
        $this->helper = $helper;
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
            $this->loadedData =
            $this->helper->buildDynamicRows(
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
