<?php
namespace Ziffity\CustomFrame\Model\ResourceModel\QuantityClassification;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	
	protected function _construct()
	{
		$this->_init('Ziffity\CustomFrame\Model\QuantityClassification', 'Ziffity\CustomFrame\Model\ResourceModel\QuantityClassification');
	}

}