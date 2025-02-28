<?php

namespace Ziffity\ProductCustomizer\Plugin;

use Magento\Quote\Model\Quote\Item;
use \Magento\Framework\Webapi\Rest\Request;

class AddItemPlugin
{

    protected $_request;

    public function __construct(
        Request $_request
    )
    {
        $this->_request = $_request;
    }

    public function afterRepresentProduct(Item $subject, $result, $product)
    {
       if($product->getTypeId() == 'customframe'){
           $updateItem = $this->_request->getParam('updateItem', 'false');
            if ($updateItem !== 'false') {
               return $result;
            }
           return false;
       }
       return $result;
    }
}
