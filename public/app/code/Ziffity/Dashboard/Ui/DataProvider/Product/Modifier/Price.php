<?php

namespace Ziffity\Dashboard\Ui\DataProvider\Product\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Framework\App\Request\DataPersistorInterface;

class Price extends AbstractModifier
{

    protected $locator;

    private $dataPersistor;

    public function __construct(
        LocatorInterface $locator,
        DataPersistorInterface $dataPersistor
    ) {
        $this->locator = $locator;
        $this->dataPersistor = $dataPersistor;
    }


    public function modifyData(array $data)
    {
        if (!$this->locator->getProduct()->getId() && $this->dataPersistor->get('catalog_product')) {
            return $this->resolvePersistentData($data);
        }
        $productId = $this->locator->getProduct()->getId();
        $productPrice =  $this->locator->getProduct()->getPrice();
        $materialCost = $this->locator->getProduct()->getMaterialCost();
        $data[$productId][self::DATA_SOURCE_DEFAULT]['price'] = number_format((float)$productPrice, 4, '.', '');
        $data[$productId][self::DATA_SOURCE_DEFAULT]['material_cost'] = number_format((float)$materialCost, 4, '.', '');
        return $data;
    }



    public function modifyMeta(array $meta)
    {
        return $meta;
    }


}
