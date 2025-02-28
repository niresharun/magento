<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Backend;

use Ziffity\CustomFrame\Helper\Data as Helper;

class SaveOpeningSize extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Helper $helper
     */
    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Before Attribute Save Process
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $openingSize = [];
        $canSaveOpening = $this->helper
            ->checkPrimaryProductsOptions($object, ['Openings','Headers','Labels']);
        if ($object->getOpeningSize() && $canSaveOpening) {
            $openingSizeData = json_decode($object->getOpeningSize(), true);
            if (isset($openingSizeData['modules']['mat']['sizes'])) {
                $openingSize = $openingSizeData['modules']['mat']['sizes'];
            }
            if ($openingSizeData) {
                if (!empty($openingSizeData['modules']['mat']['sizes_lock'])) {
                    $openingSize['sizes_lock'] = $openingSizeData['modules']['mat']['sizes_lock'];
                }
            }
            empty($openingSize) ? $object->setData($attributeCode, null) :
                $object->setData($attributeCode, json_encode($openingSize));
        }
        if (!$canSaveOpening) {
            $this->setNullValue($object, $attributeCode);
        }
        return $this;
    }

    /**
     * This function sets the null value to opening size attribute.
     *
     * @param object $object
     * @param string $attributeCode
     * @return void
     */
    public function setNullValue($object, $attributeCode)
    {
        $object->setData($attributeCode, null);
    }
}
