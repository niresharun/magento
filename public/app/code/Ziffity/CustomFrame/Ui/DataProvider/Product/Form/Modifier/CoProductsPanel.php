<?php

declare(strict_types=1);

namespace Ziffity\CustomFrame\Ui\DataProvider\Product\Form\Modifier;

use Magento\Bundle\Model\Product\Attribute\Source\Shipment\Type as ShipmentType;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Modal;
use Magento\Store\Model\Store;
use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePrice;
use Ziffity\CustomFrame\Model\Product\Type;
use Ziffity\CustomFrame\Model\ResourceModel\QuantityClassification\CollectionFactory;

/**
 * Create Co products Items and Affect Bundle Product Selections fields
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CoProductsPanel extends AbstractModifier
{
    public const GROUP_CONTENT = 'content';
    public const CODE_COPRODUCTS_DATA = 'coproducts-items';
    public const CODE_AFFECT_COPRODUCTS_PRODUCT_SELECTIONS = 'affect_bundle_product_selections';
    public const CODE_COPRODUCTS_HEADER = 'coproducts_header';
    public const CODE_COPRODUCTS_OPTIONS = 'co_product_options';
    public const SORT_ORDER = 20;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ShipmentType
     */
    protected $shipmentType;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var CollectionFactory
     */
    protected $listCollections;

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param ShipmentType $shipmentType
     * @param ArrayManager $arrayManager
     * @param CollectionFactory $listCollections
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        ShipmentType $shipmentType,
        ArrayManager $arrayManager,
        CollectionFactory $listCollections
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->shipmentType = $shipmentType;
        $this->arrayManager = $arrayManager;
        $this->listCollections = $listCollections;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modifyMeta(array $meta)
    {
        $productId = $this->locator->getProduct()->getTypeId();
        if ($productId == Type::TYPE_CODE) {

            $meta = $this->removeFixedTierPrice($meta);

            $groupCode = static::CODE_COPRODUCTS_DATA;
            $path = $this->arrayManager->findPath($groupCode, $meta, null, 'children');
            if (empty($path)) {
                $meta[$groupCode]['children'] = [];
               // $meta['bundle-items']['children'] = [];
                $meta[$groupCode]['arguments']['data']['config'] = [
                    'componentType' => Fieldset::NAME,
                    'label' => __('Co-Products'),
                    'collapsible' => true
                ];

                $path = $this->arrayManager->findPath($groupCode, $meta, null, 'children');
            }

            $meta = $this->arrayManager->merge(
                $path,
                $meta,
                [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataScope' => '',
                                'opened' => false,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $meta,
                                    static::GROUP_CONTENT,
                                    static::SORT_ORDER
                                )
                            ],
                        ],
                    ],
                    'children' => [
                        'modal' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'isTemplate' => false,
                                        'componentType' => Modal::NAME,
                                        'dataScope' => '',
                                        'opened' => true,
                                        'provider' => 'product_form.product_form_data_source',
                                        'options' => [
                                            'title' => __('Add Products to Option'),
                                            'buttons' => [
                                                [
                                                    'text' => __('Cancel'),
                                                    'actions' => ['closeModal'],
                                                ],
                                                [
                                                    'text' => __('Add Selected Products'),
                                                    'class' => 'action-primary',
                                                    'actions' => [
                                                        [
                                                            'targetName' => 'index = co_product_listing',
                                                            'actionName' => 'save'
                                                        ],
                                                        'closeModal'
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'children' => [
                                'co_product_listing' => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'autoRender' => false,
                                                'componentType' => 'insertListing',
                                                'dataScope' => 'co_product_listing',
                                                'externalProvider' =>
                                                    'co_product_listing.co_product_listing_data_source',
                                                'selectionsProvider' =>
                                                    'co_product_listing.co_product_listing.product_columns.ids',
                                                'ns' => 'co_product_listing',
                                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                                'realTimeLink' => false,
                                                'dataLinks' => ['imports' => false, 'exports' => true],
                                                'behaviourType' => 'simple',
                                                'externalFilterMode' => true,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        self::CODE_AFFECT_COPRODUCTS_PRODUCT_SELECTIONS => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Form\Field::NAME,
                                        'dataType' => Form\Element\DataType\Text::NAME,
                                        'formElement' => Form\Element\Input::NAME,
                                        'dataScope' => 'data.affect_bundle_product_selections',
                                        'visible' => false,
                                        'value' => '1'
                                    ],
                                ],
                            ],
                        ],
                        self::CODE_COPRODUCTS_OPTIONS => $this->getCoProductOptions()
                    ]
                ]
            );

            //TODO: Remove this workaround after MAGETWO-49902 is fixed
            $bundleItemsGroup = $this->arrayManager->get($path, $meta);
            $meta = $this->arrayManager->remove($path, $meta);
            $meta = $this->arrayManager->set($path, $meta, $bundleItemsGroup);

        }
         return $meta;
    }

    /**
     * Remove option with fixed tier price from config.
     *
     * @param array $meta
     * @return array
     */
    private function removeFixedTierPrice(array $meta)
    {
        $tierPricePath = $this->arrayManager->findPath(
            ProductAttributeInterface::CODE_TIER_PRICE,
            $meta,
            null,
            'children'
        );
        $pricePath =  $this->arrayManager->findPath(
            ProductAttributeInterface::CODE_TIER_PRICE_FIELD_PRICE,
            $meta,
            $tierPricePath
        );
        $pricePath = $this->arrayManager->slicePath($pricePath, 0, -1) . '/value_type/arguments/data/options';

        $price = $this->arrayManager->get($pricePath, $meta);
        if ($price) {
            $meta = $this->arrayManager->remove($pricePath, $meta);
            foreach ($price as $key => $item) {
                if ($item['value'] == ProductPriceOptionsInterface::VALUE_FIXED) {
                    unset($price[$key]);
                }
            }
            $meta = $this->arrayManager->merge(
                $this->arrayManager->slicePath($pricePath, 0, -1),
                $meta,
                ['options' => $price]
            );
        }

        return $meta;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }


    /**
     * Get bundle header structure
     *
     * @return array
     */
    protected function getBundleHeader()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => 10,
                    ],
                ],
            ],
            'children' => [
                'add_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'title' => __('Add Products'),
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/form/components/button',
                                'sortOrder' => 20,
                                'actions' => [
                                    [
                                        'targetName' => 'product_form.product_form.'
                                            . self::CODE_COPRODUCTS_DATA . '.' . self::CODE_COPRODUCTS_OPTIONS,
                                        'actionName' => 'processingAddChild',
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get Bundle Options structure
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getCoProductOptions()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'component' => 'Magento_Bundle/js/components/bundle-dynamic-rows',
                        'template' => 'ui/dynamic-rows/templates/collapsible',
                        'additionalClasses' => 'admin__field-wide',
                        'dataScope' => 'data.co_product_options',
                        'isDefaultFieldScope' => 'is_default',
                        'opened' => true,
                        'defaultRecord' => true,
                        'bundleSelectionsName' => 'product_bundle_container.bundle_selections',
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'opened' => true,
                                'headerLabel' => __('Products'),
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => 'product_bundle_container.position',
                                'imports' => [
                                    'label' => '${ $.name }' . '.product_bundle_container.option_info.title:value',
                                    '__disableTmpl' => ['label' => false],
                                ],
                            ],
                        ],
                    ],
                    'children' => [
                        'product_bundle_container' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'fieldset',
                                        'collapsible' => true,
                                        'label' => '',
                                        'opened' => true,
                                    ],
                                ],
                            ],
                            'children' => [
                                'option_info' => $this->getOptionInfo(),
                                'position' => $this->getHiddenColumn('position', 20),
                                'option_id' => $this->getHiddenColumn('option_id', 30),
                                'bundle_selections' => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'componentType' => Container::NAME,
                                                'component' => 'Ziffity_CustomFrame/js/components/customframe-dynamic-rows-grid',
                                                'sortOrder' => 50,
                                                'opened' => true,
                                                'additionalClasses' => 'admin__field-wide',
                                                'template' => 'Ziffity_CustomFrame/components/customframe-dynamic-rows-grid',
                                                'sizesConfig' => [
                                                    'enabled' => true
                                                ],
                                                'provider' => 'product_form.product_form_data_source',
                                                'dataProvider' => '${ $.dataScope }' . '.bundle_button_proxy',
                                                '__disableTmpl' => ['dataProvider' => false],
                                                'identificationDRProperty' => 'product_id',
                                                'identificationProperty' => 'product_id',
                                                'map' => [
                                                    'product_id' => 'entity_id',
                                                    'name' => 'name',
                                                    'sku' => 'sku',
                                                    'price' => 'price',
                                                    'delete' => '',
                                                    'selection_can_change_qty' => '',
                                                    'selection_id' => '',
                                                    'selection_price_type' => '',
                                                    'selection_price_value' => '',
                                                    'selection_qty' => '',
                                                    'product_quantity_classification' => 'product_quantity_classification'
                                                ],
                                                'links' => [
                                                    'insertData' => '${ $.provider }:${ $.dataProvider }',
                                                    '__disableTmpl' => ['insertData' => false],
                                                ],
                                                'imports' => [
                                                    'inputType' => '${$.provider}:${$.dataScope}.type',
                                                    '__disableTmpl' => ['inputType' => false],
                                                ],
                                                'source' => 'product',
                                            ],
                                        ],
                                    ],
                                    'children' => [
                                        'record' => $this->getBundleSelections(),
                                    ]
                                ],
                                'modal_set' => $this->getModalSet(),
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Prepares configuration for the hidden columns
     *
     * @param string $columnName
     * @param int $sortOrder
     * @return array
     */
    protected function getHiddenColumn($columnName, $sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'dataType' => Form\Element\DataType\Text::NAME,
                        'formElement' => Form\Element\Input::NAME,
                        'dataScope' => $columnName,
                        'visible' => false,
                        'additionalClasses' => ['_hidden' => true],
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get configuration for the modal set: modal and trigger button
     *
     * @return array
     */
    protected function getModalSet()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'sortOrder' => 60,
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'dataScope' => 'bundle_button_proxy',
                        'component' => 'Magento_Catalog/js/bundle-proxy-button',
                        'provider' => 'product_form.product_form_data_source',
                        'listingDataProvider' => 'co_product_listing',
                        'actions' => [
                            [
                                'targetName' => 'product_form.product_form.' . static::CODE_COPRODUCTS_DATA . '.modal',
                                'actionName' => 'toggleModal'
                            ],
                            [
                                'targetName' => 'product_form.product_form.' . static::CODE_COPRODUCTS_DATA
                                    . '.modal.co_product_listing',
                                'actionName' => 'render'
                            ]
                        ],
                        'title' => __('Add Products to Option'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Get configuration for option title
     *
     * @return array
     */
    protected function getTitleConfiguration()
    {
        $result['title']['arguments']['data']['config'] = [
            'dataType' => Form\Element\DataType\Text::NAME,
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataScope' => $this->isDefaultStore() ? 'title' : 'default_title',
            'label' => $this->isDefaultStore() ? __('Option Title') : __('Default Title'),
            'value' => 'Co-Products',
            'sortOrder' => 10,
            'validation' => ['required-entry' => false],
        ];

        if (!$this->isDefaultStore()) {
            $result['store_title']['arguments']['data']['config'] = [
                'dataType' => Form\Element\DataType\Text::NAME,
                'formElement' => Form\Element\Input::NAME,
                'componentType' => Form\Field::NAME,
                'dataScope' => 'title',
                'value' => 'Co-Products',
                'label' => __('Store View Title'),
                'sortOrder' => 15,
                'validation' => ['required-entry' => false],
            ];
        }

        return $result;
    }

    /**
     * Get option info
     *
     * @return array
     */
    protected function getOptionInfo()
    {
        $result = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/group',
                        'showLabel' => false,
                        'visible' => false,
                        'additionalClasses' => ['_hidden' => true],
                        'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                        'breakLine' => false,
                        'sortOrder' => 10,
                    ],
                ],
            ],
            'children' => [
                'type' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'formElement' => Form\Element\Select::NAME,
                                'componentType' => Form\Field::NAME,
                                'component' => 'Magento_Ui/js/form/element/select',
                                'parentContainer' => 'product_bundle_container',
                                'selections' => 'bundle_selections',
                                'isDefaultIndex' => 'is_default',
                                'additionalClasses' => ['_hidden' => true],
                                'userDefinedIndex' => 'selection_can_change_qty',
                                'dataScope' => 'type',
                                'label' => __('Input Type'),
                                'sortOrder' => 20,
                                'options' => [
                                    [
                                        'label' => __('Drop-down'),
                                        'value' => 'select'
                                    ],
                                    [
                                        'label' => __('Radio Buttons'),
                                        'value' => 'radio'
                                    ],
                                    [
                                        'label' => __('Checkbox'),
                                        'value' => 'checkbox'
                                    ],
                                    [
                                        'label' => __('Multiple Select'),
                                        'value' => 'multi'
                                    ]
                                ],
                                'typeMap' => [
                                    'select' => 'radio',
                                    'radio' => 'radio',
                                    'checkbox' => 'checkbox',
                                    'multi' => 'checkbox'
                                ]
                            ],
                        ],
                    ],
                ],
                'required' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => Form\Element\DataType\Number::NAME,
                                'formElement' => Form\Element\Checkbox::NAME,
                                'componentType' => Form\Field::NAME,
                                'description' => __('Required'),
                                'dataScope' => 'required',
                                'additionalClasses' => ['_hidden' => true],
                                'label' => ' ',
                                'value' => '1',
                                'valueMap' => [
                                    'true' => '1',
                                    'false' => '0',
                                ],
                                'sortOrder' => 30,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $this->arrayManager->merge('children', $result, $this->getTitleConfiguration());
    }

    /**
     * Get bundle selections structure
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getBundleSelections()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'isTemplate' => true,
                        'component' => 'Magento_Ui/js/dynamic-rows/record',
                        'is_collection' => true,
                        'imports' => [
                            'inputType' => '${$.parentName}:inputType',
                            '__disableTmpl' => ['inputType' => false],
                        ],
                        'exports' => [
                            'isDefaultValue' => '${$.parentName}:isDefaultValue.${$.index}',
                            '__disableTmpl' => ['isDefaultValue' => false],
                        ],
                    ],
                ],
            ],
            'children' => [
                'selection_id' => $this->getHiddenColumn('selection_id', 10),
                'option_id' => $this->getHiddenColumn('option_id', 20),
                'product_id' => $this->getHiddenColumn('product_id', 30),
                'name' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'formElement' => Form\Element\Input::NAME,
                                'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                'label' => __('Name'),
                                'dataScope' => 'name',
                                'sortOrder' => 60,
                            ],
                        ],
                    ],
                ],
                'sku' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'formElement' => Form\Element\Input::NAME,
                                'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                'label' => __('SKU'),
                                'dataScope' => 'sku',
                                'sortOrder' => 70,
                            ],
                        ],
                    ],
                ],
                'position' => $this->getHiddenColumn('position', 120),
                'action_delete' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'actionDelete',
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'label' => '',
                                'fit' => true,
                                'sortOrder' => 130,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get selection price value structure
     *
     * @return array
     */
    protected function getSelectionPriceValue()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'dataType' => Form\Element\DataType\Price::NAME,
                        'formElement' => Form\Element\Input::NAME,
                        'label' => __('Price'),
                        'dataScope' => 'selection_price_value',
                        'value' => '0.00',
                        'imports' => [
                            'visible' => '!ns = ${ $.ns }, index = ' . BundlePrice::CODE_PRICE_TYPE . ':checked',
                            '__disableTmpl' => ['visible' => false],
                        ],
                        'sortOrder' => 80,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get selection price type structure
     *
     * @return array
     */
    protected function getSelectionPriceType()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'dataType' => Form\Element\DataType\Boolean::NAME,
                        'formElement' => Form\Element\Select::NAME,
                        'label' => __('Price Type'),
                        'dataScope' => 'selection_price_type',
                        'value' => '0',
                        'options' => [
                            [
                                'label' => __('Fixed'),
                                'value' => '0'
                            ],
                            [
                                'label' => __('Percent'),
                                'value' => '1'
                            ]
                        ],
                        'imports' => [
                            'visible' => '!ns = ${ $.ns }, index = ' . BundlePrice::CODE_PRICE_TYPE . ':checked',
                            '__disableTmpl' => ['visible' => false],
                        ],
                        'sortOrder' => 90,
                    ],
                ],
            ],
        ];
    }

    /**
     * Check that store is default
     *
     * @return bool
     */
    protected function isDefaultStore()
    {
        return $this->locator->getProduct()->getStoreId() == Store::DEFAULT_STORE_ID;
    }

    /**
     * Get Product quantity classification lists
     */
    public function getListOptions()
    {
        $options = [];
        array_push($options, ["value" => null, 'label' => 'Please Select']);
        $lists = $this->listCollections->create();
        foreach ($lists as $list) {
            if ($list && $list->getId()) {
                array_push($options, ["value" => $list->getId(), 'label' => $list->getListName()]);
            }
        }
        return $options;
    }

}
