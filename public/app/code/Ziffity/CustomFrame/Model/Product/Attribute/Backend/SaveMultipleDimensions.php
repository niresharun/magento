<?php

namespace Ziffity\CustomFrame\Model\Product\Attribute\Backend;

use Ziffity\CustomFrame\Helper\Data as Helper;

class SaveMultipleDimensions extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{

    /**
     * @var Helper
     */
    public $helper;

    /**
     * @var string[]
     */
    public $multiselectAttributes = [
        'template_sizes',
        'template_product_type',
        'template_orientation',
        'template_frame_material',
        'template_locking',
        'template_matboard_included',
        'template_multiple_mat_openings',
        'template_with_header',
        'template_with_labels',
        'graphic_thickness_interior_depth',
        'dimension_2_default',
        'dimension_1_default',
        'dimension_2',
        'dimension_1',
        'fabric_selections',
        'laminate_colors',
        'template_letter_set_colors',
        'template_letter_set_font',
        'template_letter_set_sizes',
        'template_letter_set_type',
        'template_backer_board_type',
        'template_featured_selections',
        'template_frame_colors',
        'frame_color',
        'frame_featured_selections',
        'featured_selections',
        'frame_finish',
        'frame_material',
        'frame_rabbet_depth',
        'frame_shape',
        'frame_style',
        'frame_tone',
        'frame_width',
        'nielsen_profile',
        'template_frame_width',
        'template_interior_depths',
        'template_letterboard_colors',
        'template_letterboard_material',
        'template_menu_orientation',
        'template_menu_pages',
        'template_mounted_graphics',
        'template_outdoor_application',
        'template_total_matboard',
        'template_with_lights',
        'template_with_shelves',
        'mat_type',
        'additional_tabs'
    ];

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
        if (in_array($attributeCode, $this->multiselectAttributes)) {
            $data = $object->getData($attributeCode);
            if (!is_array($data)) {
                $data = $data ? explode(',', $data): [];
            }
            $object->setData($attributeCode, implode(',', $data) ?: null);
        }
        if (!$object->hasData($attributeCode)) {
            $object->setData($attributeCode, null);
        }
        return $this;
    }

    /**
     * After Load Attribute Process
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if (in_array($attributeCode, $this->multiselectAttributes)) {
            $data = $object->getData($attributeCode);
            if ($data) {
                if (!is_array($data)) {
                    $object->setData($attributeCode, explode(',', $data));
                } else {
                    $object->setData($attributeCode, $data);
                }
            }
        }
        return $this;
    }
}
