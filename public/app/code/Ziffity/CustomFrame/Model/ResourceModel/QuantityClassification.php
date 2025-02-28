<?php

namespace Ziffity\CustomFrame\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\Context;

class QuantityClassification extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	protected function _construct()
	{
		$this->_init('product_quantity_classification', 'id');
	}
	
}