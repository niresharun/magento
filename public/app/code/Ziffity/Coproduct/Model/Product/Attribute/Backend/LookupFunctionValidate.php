<?php

namespace Ziffity\Coproduct\Model\Product\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\Exception\LocalizedException;
use Ziffity\Coproduct\Model\Evaluate;

class LookupFunctionValidate extends AbstractBackend
{

    protected $lookupFunction;

    public function __construct(Evaluate $lookupFunction)
    {
        $this->lookupFunction = $lookupFunction;
    }

    public function validate($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        //TODO: Need to validate the formula in phase-2 of this project.
//        if (!$this->lookupFunction->checkPatternMatched($object->getData($attrCode))){
//            throw new LocalizedException(
//                __('The "%1" attribute value does not match the lookup formula.', $attrCode)
//            );
//        }
        return true;
    }
}
