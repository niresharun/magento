<?php
namespace Ziffity\Coproduct\Model\Rule\Condition;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param CollectionFactory $productAttributeCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        private CollectionFactory $productAttributeCollection,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->setType(\Ziffity\Coproduct\Model\Rule\Condition\Combine::class);
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $attributes = [];

        $attributesList = $this->productAttributeCollection->create();
        $attributesList->addFieldToFilter('additional_table.is_used_for_promo_rules', ['eq' => 1])
            ->setAttributeSetFilterBySetName('Custom Frame', 'catalog_product');
        if ($attributesList->getSize()) {
            foreach ($attributesList as $attribute) {
                $attributes[] = [
                    'value' => 'Ziffity\Coproduct\Model\Rule\Condition\CatalogProduct|' . $attribute->getAttributeCode(),
                    'label' => $attribute->getFrontendLabel(),
                ];
            }


        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => \Ziffity\Coproduct\Model\Rule\Condition\Combine::class,
                    'label' => __('Conditions Combination '),
                ],
                [
                    'value' => 'Ziffity\Coproduct\Model\Rule\Condition\CatalogProduct|attribute_set_id',
                    'label' => 'Attribute Set',
                ],
                [
                    'value' => 'Ziffity\Coproduct\Model\Rule\Condition\Product|overall_height',
                    'label' => 'Overall Frame Height',
                ],
                [
                    'value' => 'Ziffity\Coproduct\Model\Rule\Condition\Product|overall_width',
                    'label' => 'Overall Frame Width',
                ],
                ['label' => __('CustomFrame Product Attribute'), 'value' => $attributes]
            ]
        );

        $linkTypes = [
            'frame'             => ['attribute_set_name' => 'Frame', 'label' => 'Frame'],
            'top_mat'           => ['attribute_set_name' => 'Mat', 'label' => 'Top Mat'],
            'bottom_mat'        => ['attribute_set_name' => 'Mat', 'label' => 'Bottom Mat'],
            'middle_mat'        => ['attribute_set_name' => 'Mat', 'label' => 'Middle Mat'],
            'fabric'            => ['attribute_set_name' => 'Fabric', 'label' => 'Fabric'],
            'dryeraseboard'    => ['attribute_set_name' => 'Dry Erase Board', 'label' => 'Dry Erase Board'],
            'chalkboard'       => ['attribute_set_name' => 'Chalk Boards', 'label' => 'Chalk Boards'],
            'glass'             => ['attribute_set_name' => 'Glass', 'label' => 'Glass'],
            'postfinish'       => ['attribute_set_name' => 'Post Finish', 'label' => 'Post Finish'],
            'backingboard'     => ['attribute_set_name' => 'Backing Board', 'label' => 'Backing Board'],
            'laminate_interior' => ['attribute_set_name' => 'Laminate', 'label' => 'Laminate Interior'],
            'laminate_exterior' => ['attribute_set_name' => 'Laminate', 'label' => 'Laminate Exterior'],
            'letterboard'      => ['attribute_set_name' => 'Letter Board', 'label' => 'Letter Board'],
            'corkboard'        => ['attribute_set_name' => 'Cork Boards', 'label' => 'Cork Board']
        ];

        foreach ($linkTypes as $code => $linkData) {
            $attributes = [];
            $attributesList = $this->productAttributeCollection->create()->addFieldToFilter('additional_table.is_used_for_promo_rules', ['eq' => 1]);
            $attributesList->setAttributeSetFilterBySetName($linkData['attribute_set_name'], 'catalog_product');
            if ($attributesList->getSize()) {
                foreach ($attributesList as $attribute) {
                    $attributes[] = [
                        'value' => 'Ziffity\Coproduct\Model\Rule\Condition\AssociatedProduct|' . $code . '>'
                            . $attribute->getAttributeCode(),
                        'label' => $attribute->getFrontendLabel(),
                    ];
                }
                if (count($attributes)) {
                    $conditions = array_merge_recursive(
                        $conditions,
                        [
                            [
                                'label' =>  __('%1 Product Attribute', $linkData['label']),
                                'value' => $attributes,
                            ],
                        ]
                    );
                }
            }
        }

        return $conditions;
    }
}
