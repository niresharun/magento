<?php
namespace Ziffity\Coproduct\Model\Product\Type;

class Coproduct extends \Magento\Catalog\Model\Product\Type\Simple
{
    const TYPE_CODE = 'coproduct';

    /**
     * Delete data specific for Simple product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */

    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
    }
}
