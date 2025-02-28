<?php
namespace Ziffity\Coproduct\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Ziffity\Coproduct\Model\Product\Type\Coproduct;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Api\AttributeManagementInterface;

class CreateConditionAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    private $attributeSetFactory;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * CreateOpeningAttributes constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param CategorySetupFactory $categorySetupFactory
     * @param AttributeManagementInterface $attributeManagement
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        CategorySetupFactory $categorySetupFactory,
        private AttributeManagementInterface $attributeManagement
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attributeSet = $this->attributeSetFactory->create();
        $entityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
        $data = [
            'attribute_set_name' => 'Coproduct',
            'entity_type_id' => $entityTypeId,
            'sort_order' => 50,
        ];
        $attributeSet->setData($data);
        $attributeSet->validate();
        $attributeSet->save();
        $attributeSet->initFromSkeleton($attributeSetId)->save();
        $newAttributeSetId = $attributeSet->getAttributeSetId();

        // TODO: Clean unnesscary attributes from the coproduct attribute set.
        /*$unassignAttributeList = [
            'box_depth',
            'box_dimension',
            'expedited_shipping',
            'header_data',
            'label_data',
            'shipping_alert_qty',
            'opening_data',
            'opening_size',
            'shipping_profile',
            'show_qty_alert',
            'price',
            'price_type',
            'giftcard_type',
            'giftcard_amounts',
            'allow_open_amount',
            'open_amount_min',
            'open_amount_max',
            'tax_class_id',
            'quantity_and_stock_status',
            'weight',
            'weight_type',
            'visibility',
            'category_ids',
            'news_from_date',
            'news_to_date',
            'country_of_manufacture',
            'is_returnable',
            'hide_quote_buy_button',
            'frames',
            'oversize_profile',
            'backingboard_type',
            'short_description',
            'description',
            'Bundle Items',
            'shipment_type',
            'Images',
            'image',
            'small_image',
            'thumbnail',
            'swatch_image',
            'media_gallery',
            'gallery',
            'url_key',
            'meta_title',
            'meta_keyword',
            'meta_description',
            'Advanced Pricing',
            'special_price',
            'special_from_date',
            'special_to_date',
            'cost',
            'tier_price',
            'price_view',
            'msrp',
            'msrp_display_actual_price_type',
            'page_layout',
            'options_container',
            'custom_layout_update_file',
            'Schedule Design Update',
            'custom_design_from',
            'custom_design_to',
            'custom_design',
            'custom_layout',
            'gift_message_available',
            'gift_wrapping_available',
            'gift_wrapping_price',
            'adg_image_banner',
            'featured_product',
            'is_bestseller',
            'is_carousel',
            'box_thickness',
            'matboard_overlap',
            'nickname_sizes',
            'is_recurring',
            'recurring_profile',
            'Product Videos',
            'product_videos',
            'template_letter_set_colors',
            'template_letter_set_font',
            'template_letter_set_sizes',
            'template_letter_set_type'
        ];
        foreach ($unassignAttributeList  as $attributeCode) {
            $this->attributeManagement->unassign(
                $newAttributeSetId,
                $attributeCode
            );
        }*/


        $eavSetup->addAttribute(
            Product::ENTITY,
            'conditions',
            [
                'type' => 'text',
                'label' => 'Eligibility Conditions',
                'required' => false,
                'sort_order' => 30,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => false,
                'apply_to'=> Coproduct::TYPE_CODE,
                'used_in_product_listing' => true
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            'applicable_to',
            [
                'type' => 'text',
                'label' => 'Applicable To',
                'input' => 'select',
                'source' => 'Ziffity\Coproduct\Model\Config\Source\ApplicableTo',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'=> Coproduct::TYPE_CODE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => 'Custom Frame',
                'used_in_product_listing' => true
            ]
        );

        $attributeCode = 'applicable_to';

        $eavSetup->addAttributeToSet(
            'catalog_product',
            $newAttributeSetId,
            null,
            $attributeCode
        );

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // associate these attributes with new product type
        $fieldList = [
            'glass_type',
            'backingboard_type'
        ];

        // make these attributes applicable to new product type and updates
        foreach ($fieldList as $field) {
            $applyTo = explode(
                ',',
                $eavSetup->getAttribute(Product::ENTITY, $field, 'apply_to')
            );
            if (!in_array(Coproduct::TYPE_CODE, $applyTo)) {
                $applyTo[] = Coproduct::TYPE_CODE;
                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $field,
                    'apply_to',
                    implode(',', $applyTo)
                );
            }
            $eavSetup->updateAttribute(Product::ENTITY, $field, 'is_required', false);

            $eavSetup->addAttributeToSet(
                'catalog_product',
                $newAttributeSetId,
                null,
                $field
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            \Ziffity\CustomFrame\Setup\Patch\Data\InstallM1Attributes::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}

