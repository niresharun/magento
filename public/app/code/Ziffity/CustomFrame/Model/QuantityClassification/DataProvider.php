<?php
namespace Ziffity\CustomFrame\Model\QuantityClassification;

use \Ziffity\CustomFrame\Model\ResourceModel\QuantityClassification\CollectionFactory;
use \Magento\Framework\Serialize\Serializer\Json;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Json
     */
    protected $serializer;

    protected $loadedData = [];


    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Json $serializer
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        Json $serializer,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->serializer = $serializer;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $rowArray = [];

        $items = $this->collection->getItems();
        $this->loadedData = array();
        /** @var Customer $customer */
        foreach ($items as $list) {
            $this->loadedData[$list->getId()]['quantity_classification'] = $list->getData();
            if ($list->getClassification()) {
                $rows = $this->serializer->unserialize($list->getClassification());
                if($rows) {
                    foreach($rows as $key => $row) {
                        $rowArray[$key] =  array( 
                                "size_from" => $row['size_from'], 
                                "size_to" => $row['size_to'],
                                "qty"  => $row['qty'], 
                                "record_id" => $row['record_id'] 
                        );
                    }
                }
                $this->loadedData[$list->getId()]['quantity_classification']['dynamic_rows']['dynamic_rows'] = $rowArray;
            }
        }
        return $this->loadedData;
    }
}