<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ziffity\CustomFrame\Ui\DataProvider\Product\Form\Modifier;

use Magento\Bundle\Model\Product\Attribute\Source\Shipment\Type as ShipmentType;
use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePrice;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Modal;
use Ziffity\CustomFrame\Block\Adminhtml\Edit\Header as HeaderBlock;
use Ziffity\CustomFrame\Block\Adminhtml\Edit\Opening as OpeningBlock;
use Ziffity\CustomFrame\Block\Adminhtml\Edit\Label as LabelBlock;
use Ziffity\CustomFrame\Model\Product\Attribute\Source\Type\CommonDimensionsOptions as HeaderHeight;
use Ziffity\CustomFrame\Model\Product\Attribute\Source\Type\HeaderType as HeaderPosition;
use Ziffity\CustomFrame\Helper\Data as Helper;
use Magento\Framework\Registry;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;

/**
 * Create Ship Bundle Items and Affect Bundle Product Selections fields
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundlePanel extends AbstractModifier
{
    public const GROUP_CONTENT = 'content';
    public const CODE_SHIPMENT_TYPE = 'shipment_type';
    public const CODE_BUNDLE_DATA = 'bundle-items';
    public const CODE_AFFECT_BUNDLE_PRODUCT_SELECTIONS = 'affect_bundle_product_selections';
    public const CODE_BUNDLE_HEADER = 'bundle_header';
    public const CODE_BUNDLE_OPTIONS = 'bundle_options';
    public const SORT_ORDER = 20;

    protected $registry;

    /**
     * @var Helper
     */
    protected $helper;

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
     * @var HeaderHeight
     */
    protected $headerHeight;

    /**
     * @var HeaderPosition
     */
    protected $headerPosition;

    /**
     * @var HeaderBlock
     */
    protected $headerBlock;

    /**
     * @var OpeningBlock
     */
    protected $openingBlock;

    /**
     * @var LabelBlock
     */
    protected $labelBlock;

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param ShipmentType $shipmentType
     * @param ArrayManager $arrayManager
     * @param OpeningBlock $openingBlock
     * @param HeaderBlock $headerBlock
     * @param LabelBlock $labelBlock
     * @param HeaderPosition $headerPosition
     * @param HeaderHeight $headerHeight
     * @param Helper $helper
     * @param Registry $registry
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        ShipmentType $shipmentType,
        ArrayManager $arrayManager,
        OpeningBlock     $openingBlock,
        HeaderBlock      $headerBlock,
        LabelBlock $labelBlock,
        HeaderPosition   $headerPosition,
        HeaderHeight     $headerHeight,
        Helper $helper,
        Registry $registry
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->shipmentType = $shipmentType;
        $this->arrayManager = $arrayManager;
        $this->openingBlock = $openingBlock;
        $this->headerBlock = $headerBlock;
        $this->labelBlock = $labelBlock;
        $this->headerPosition = $headerPosition;
        $this->headerHeight = $headerHeight;
        $this->helper = $helper;
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->removeFixedTierPrice($meta);

        $groupCode = static::CODE_BUNDLE_DATA;
        $path = $this->arrayManager->findPath($groupCode, $meta, null, 'children');
        if (empty($path)) {
            $meta[$groupCode]['children'] = [];
            $meta[$groupCode]['arguments']['data']['config'] = [
                'componentType' => Fieldset::NAME,
                'label' => __('Bundle Items'),
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
                            'opened' => true,
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
                                                        'targetName' => 'index = bundle_product_listing',
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
                            'bundle_product_listing' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'autoRender' => false,
                                            'componentType' => 'insertListing',
                                            'dataScope' => 'bundle_product_listing',
                                            'externalProvider' => 'bundle_product_listing.bundle_product_listing_data_source',
                                            'selectionsProvider' => 'bundle_product_listing.bundle_product_listing.product_columns.ids',
                                            'ns' => 'bundle_product_listing',
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
                    self::CODE_AFFECT_BUNDLE_PRODUCT_SELECTIONS => [
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
                    self::CODE_BUNDLE_HEADER => $this->getBundleHeader(),
                    self::CODE_BUNDLE_OPTIONS => $this->getBundleOptions()
                ]
            ]
        );

        //TODO: Remove this workaround after MAGETWO-49902 is fixed
        $bundleItemsGroup = $this->arrayManager->get($path, $meta);
        $meta = $this->arrayManager->remove($path, $meta);
        $meta = $this->arrayManager->set($path, $meta, $bundleItemsGroup);

        if (isset($meta["bundle-items"]["arguments"]["data"]
                ["config"]["label"])) {
            $meta["bundle-items"]["arguments"]["data"]["config"]
                ["label"] = __('Primary Product');
        }
        if (isset($meta['bundle-items']['children']['container_shipment_type'])) {
            unset($meta["bundle-items"]["children"]["container_shipment_type"]);
        }
        if (isset($meta["bundle-items"]["children"]["bundle_options"]
                ["children"]["record"]["children"]["product_bundle_container"]
                ["children"]["option_info"]["children"]["title"])) {
            $meta["bundle-items"]["children"]["bundle_options"]
                ["children"]["record"]["children"]["product_bundle_container"]
                ["children"]["option_info"]["children"]["title"]["arguments"]
                ["data"]["config"]["formElement"] = 'select';
            $meta["bundle-items"]["children"]["bundle_options"]
                ["children"]["record"]["children"]["product_bundle_container"]
                ["children"]["option_info"]["children"]["title"]["arguments"]
                ["data"]["config"]["options"] = $this->bundleOptions();
            $meta["bundle-items"]["children"]["bundle_options"]
                ["children"]["record"]["arguments"]["data"]["config"]
                ["imports"]["label"] = '${ $.name }.product_bundle_container.option_info.title:label';
        }
        $meta = $this->changeListingForm($meta);
        $meta["size"]["arguments"]["data"]["config"]["collapsible"] = false;
        $meta["size"]["arguments"]["data"]["config"]["sortOrder"] = 996;
        $meta = $this->hideOpeningDataSizeAttributes($meta);
        $meta = $this->hideHeaderDataAttributes($meta);
        $meta = $this->hideLabelDataAttributes($meta);
        $meta = $this->showOpeningHeaderLabel($meta);
        $meta = $this->modifySizeTypeAttributes($meta);
        return $meta;
    }

    protected function modifySizeTypeAttributes($result)
    {
        if (isset($result['size']['children']['container_depth_type'])) {
            $result["size"]["children"]["container_depth_type"]["children"]
            ["depth_type"]["arguments"]["data"]["config"]["component"] =
                "Ziffity_CustomFrame/js/component/depth-type-select";
            //Manually setting the visibility to false and toggling to true in select.js file
            $result["size"]["children"]["container_graphic_thickness_interior_depth"]
            ["children"]["graphic_thickness_interior_depth"]["arguments"]["data"]
            ["config"]["visible"] = false;
            $result["size"]["children"]["container_box_thickness"]["children"]
            ["box_thickness"]["arguments"]["data"]["config"]["visible"] = false;
        }
        return $result;
    }

    protected function showOpeningHeaderLabel($meta)
    {
        $productSku = $this->registry->registry('current_product')->getSku();
        if ($productSku) {
            if ($this->helper->hasOpening($productSku)) {
                $meta = $this->modifyOpeningDataAttributes($meta);
            }
            if ($this->helper->hasHeader($productSku)) {
                $meta = $this->modifyHeaderDataAttributes($meta);
            }
            if ($this->helper->hasLabel($productSku)) {
                $meta = $this->modifyLabelDataAttributes($meta);
            }
        }
        return $meta;
    }

    protected function hideHeaderDataAttributes($result)
    {
        $result["product-details"]["children"]["container_header_data"]
        ["children"]["header_data"]["arguments"]["data"]["config"]["visible"] = false;
        $result["product-details"]["children"]["container_header_data"]
        ["arguments"]["data"]["config"]["componentType"] = Container::NAME;
        $result["product-details"]["children"]["container_header_data"]
        ["children"]["header_data"]["arguments"]["data"]["config"]["componentType"] = Field::NAME;
        $result["product-details"]["children"]["container_header_data"]
        ["children"]["header_data"]["arguments"]["data"]["config"]["formElement"] = Input::NAME;
        return $result;
    }

    protected function hideLabelDataAttributes($result)
    {
        $result["product-details"]["children"]["container_label_data"]
        ["children"]["label_data"]["arguments"]["data"]["config"]["visible"] = false;

        $result["product-details"]["children"]["container_label_data"]
        ["arguments"]["data"]["config"]["componentType"] = Container::NAME;
        $result["product-details"]["children"]["container_label_data"]
        ["children"]["label_data"]["arguments"]["data"]["config"]["componentType"] = Field::NAME;
        $result["product-details"]["children"]["container_label_data"]
        ["children"]["label_data"]["arguments"]["data"]["config"]["formElement"] = Input::NAME;

        return $result;
    }

    protected function hideOpeningDataSizeAttributes($meta)
    {
        //make visibility of opening_data and opening_size attribute to false.
        $meta["product-details"]["children"]["container_opening_data"]
        ["children"]["opening_data"]["arguments"]["data"]["config"]["visible"] = false;
        $meta["product-details"]["children"]["container_opening_data"]
        ["arguments"]["data"]["config"]["componentType"] = Container::NAME;
        $meta["product-details"]["children"]["container_opening_data"]
        ["children"]["opening_data"]["arguments"]["data"]["config"]["componentType"] = Field::NAME;
        $meta["product-details"]["children"]["container_opening_data"]
        ["children"]["opening_data"]["arguments"]["data"]["config"]["formElement"] = Input::NAME;
        $meta["product-details"]["children"]["container_opening_size"]
        ["children"]["opening_size"]["arguments"]["data"]["config"]["visible"] = false;
        $meta["product-details"]["children"]["container_opening_size"]
        ["arguments"]["data"]["config"]["componentType"] = Container::NAME;
        $meta["product-details"]["children"]["container_opening_size"]
        ["children"]["opening_size"]["arguments"]["data"]["config"]["componentType"] = Field::NAME;
        $meta["product-details"]["children"]["container_opening_size"]
        ["children"]["opening_size"]["arguments"]["data"]["config"]["formElement"] = Input::NAME;

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
     * Modify Shipment Type configuration
     *
     * @param array $meta
     * @return array
     */
    private function modifyShipmentType(array $meta)
    {
        $actualPath = $this->arrayManager->findPath(
            static::CODE_SHIPMENT_TYPE,
            $meta,
            null,
            'children'
        );

        $meta = $this->arrayManager->merge(
            $actualPath . static::META_CONFIG_PATH,
            $meta,
            [
                'dataScope' => stripos($actualPath, self::CODE_BUNDLE_DATA) === 0
                    ? 'data.product.shipment_type' : 'shipment_type',
                'validation' => [
                    'required-entry' => false
                ]
            ]
        );

        return $meta;
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
                                'title' => __('Add Option'),
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/form/components/button',
                                'sortOrder' => 20,
                                'actions' => [
                                    [
                                        'targetName' => 'product_form.product_form.'
                                            . self::CODE_BUNDLE_DATA . '.' . self::CODE_BUNDLE_OPTIONS,
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
    protected function getBundleOptions()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'component' => 'Magento_Bundle/js/components/bundle-dynamic-rows',
                        'template' => 'ui/dynamic-rows/templates/collapsible',
                        'additionalClasses' => 'admin__field-wide',
                        'dataScope' => 'data.bundle_options',
                        'isDefaultFieldScope' => 'is_default',
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
                                'headerLabel' => __('New Option'),
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
                                        'opened' => false,
                                    ],
                                ],
                            ],
                            'children' => [
                                'option_info' => $this->getOptionInfo(),
                                'position' => $this->getHiddenColumn('position', 20),
                                'option_id' => $this->getHiddenColumn('option_id', 30),
                                'delete' => $this->getHiddenColumn('delete', 40),
                                'bundle_selections' => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'componentType' => Container::NAME,
                                                'component' => 'Magento_Bundle/js/components/bundle-dynamic-rows-grid',
                                                'sortOrder' => 50,
                                                'additionalClasses' => 'admin__field-wide',
                                                'template' => 'Magento_Catalog/components/dynamic-rows-per-page',
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
                        'listingDataProvider' => 'bundle_product_listing',
                        'actions' => [
                            [
                                'targetName' => 'product_form.product_form.' . static::CODE_BUNDLE_DATA . '.modal',
                                'actionName' => 'toggleModal'
                            ],
                            [
                                'targetName' => 'product_form.product_form.' . static::CODE_BUNDLE_DATA
                                    . '.modal.bundle_product_listing',
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
            'sortOrder' => 10,
            'validation' => ['required-entry' => true],
        ];

        if (!$this->isDefaultStore()) {
            $result['store_title']['arguments']['data']['config'] = [
                'dataType' => Form\Element\DataType\Text::NAME,
                'formElement' => Form\Element\Input::NAME,
                'componentType' => Form\Field::NAME,
                'dataScope' => 'title',
                'label' => __('Store View Title'),
                'sortOrder' => 15,
                'validation' => ['required-entry' => true],
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
                'delete' => $this->getHiddenColumn('delete', 40),
                'is_default' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => Form\Element\Checkbox::NAME,
                                'componentType' => Form\Field::NAME,
                                'component' => 'Magento_Bundle/js/components/bundle-checkbox',
                                'parentContainer' => 'product_bundle_container',
                                'parentSelections' => 'bundle_selections',
                                'changer' => 'option_info.type',
                                'dataType' => Form\Element\DataType\Boolean::NAME,
                                'label' => __('Is Default'),
                                'dataScope' => 'is_default',
                                'prefer' => 'radio',
                                'value' => '0',
                                'sortOrder' => 50,
                                'valueMap' => ['false' => '0', 'true' => '1']
                            ],
                        ],
                    ],
                ],
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
                'selection_price_value' => $this->getSelectionPriceValue(),
                'selection_price_type' => $this->getSelectionPriceType(),
                'selection_qty' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'component' => 'Magento_Bundle/js/components/bundle-option-qty',
                                'formElement' => Form\Element\Input::NAME,
                                'componentType' => Form\Field::NAME,
                                'dataType' => Form\Element\DataType\Number::NAME,
                                'label' => __('Default Quantity'),
                                'dataScope' => 'selection_qty',
                                'value' => '1',
                                'sortOrder' => 100,
                                'validation' => [
                                    'required-entry' => true,
                                    'validate-number' => true,
                                    'validate-greater-than-zero' => true
                                ],
                                'imports' => [
                                    'isInteger' => '${ $.provider }:${ $.parentScope }.selection_qty_is_integer',
                                    '__disableTmpl' => ['isInteger' => false],
                                ],
                            ],
                        ],
                    ],
                ],
                'selection_can_change_qty' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Field::NAME,
                                'formElement' => Form\Element\Checkbox::NAME,
                                'dataType' => Form\Element\DataType\Price::NAME,
                                'component' => 'Magento_Bundle/js/components/bundle-user-defined-checkbox',
                                'label' => __('User Defined'),
                                'dataScope' => 'selection_can_change_qty',
                                'value' => '1',
                                'valueMap' => ['true' => '1', 'false' => '0'],
                                'sortOrder' => 110,
                                'imports' => [
                                    'inputType' => '${$.parentName}:inputType',
                                    '__disableTmpl' => ['inputType' => false],
                                ],
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
     * This function changes the visibility of opening tab to false.
     *
     * @param array $meta
     * @return array
     */
    public function modifyOpeningDataAttributes($meta)
    {
        //create opening group fieldset and htmlContent
        $meta["opening_group"]["arguments"]["data"]["config"]["componentType"] = "fieldset";
        $meta["opening_group"]["arguments"]["data"]["config"]["label"] = __("Opening Information");
        $meta["opening_group"]["arguments"]["data"]["config"]["collapsible"] = false;
        $meta["opening_group"]["arguments"]["data"]["config"]["level"] = 1;
        $meta["opening_group"]["arguments"]["data"]["config"]["visible"] = false;
        $meta["opening_group"]["children"]["opening_container"]["children"]
        ["html_content"]["arguments"]["block"] = $this->openingBlock;
        $meta["opening_group"]["children"]["opening_container"]["children"]
        ["html_content"]["arguments"]["data"]["config"]["componentType"] = "htmlContent";
        $meta["opening_group"]["children"]["opening_container"]["arguments"]
        ["data"]["config"]["sortOrder"] = "1";
        $meta["opening_group"]["children"]["opening_container"]["arguments"]
        ["data"]["config"]["componentType"] = "container";
        return $meta;
    }

    /**
     * This function changes the visibility of header tab to false.
     *
     * @param array $meta
     * @return array
     */
    public function modifyHeaderDataAttributes($meta)
    {
        //create opening group fieldset and htmlContent
        $meta["header_group"]["arguments"]["data"]["config"]["componentType"] = "fieldset";
        $meta["header_group"]["arguments"]["data"]["config"]["label"] = __("Header Information");
        $meta["header_group"]["arguments"]["data"]["config"]["collapsible"] = false;
        $meta["header_group"]["arguments"]["data"]["config"]["level"] = 1;
        $meta["header_group"]["arguments"]["data"]["config"]["visible"] = false;
        $meta["header_group"]["arguments"]["data"]["config"]["dataScope"] = "header_position";
        $meta["header_group"]["children"]["header_container"]["children"]
        ["html_content"]["arguments"]["block"] = $this->headerBlock;
        $meta["header_group"]["children"]["header_container"]["children"]
        ["html_content"]["arguments"]["data"]["config"]["componentType"] = "htmlContent";
        $meta["header_group"]["children"]["header_container"]["arguments"]
        ["data"]["config"]["sortOrder"] = "0";
        $meta["header_group"]["children"]["header_container"]["arguments"]
        ["data"]["config"]["componentType"] = "container";
        //set children

        $meta["header_group"]["children"]["container_header_position"]["arguments"]
        ["data"] = $this->buildContainer("Layout", 0);
        $meta["header_group"]["children"]["container_header_height"]["arguments"]["data"]
            = $this->buildContainer("Layout", 1);
        $meta["header_group"]["children"]["container_header_width"]["arguments"]["data"]
            = $this->buildContainer("Layout", 2);
        $meta["header_group"]["children"]["container_font_size_min"]["arguments"]["data"]
            = $this->buildContainer("Layout", 3);
        $meta["header_group"]["children"]["container_font_size_step"]["arguments"]["data"]
            = $this->buildContainer("Layout", 4);
        $meta["header_group"]["children"]["container_font_size_default"]["arguments"]["data"]
            = $this->buildContainer("Layout", 5);

        $meta["header_group"]["children"]["container_header_position"]["children"]["header_position"]["arguments"]
        ["data"] = $this->buildSelectChildren("Header Position", "header_position", "0", $this->headerPosition->getAllOptions());
        $meta["header_group"]["children"]["container_header_height"]["children"]["header_height"]["arguments"]
        ["data"] = $this->buildSelectChildren("Header Height", "header_height", "1", $this->headerHeight->getAllOptionsForAdmin());
        $meta["header_group"]["children"]["container_header_height"]["children"]["header_width"]["arguments"]
        ["data"] = $this->buildSelectChildren("Header Width", "header_width", "2", $this->headerHeight->getAllOptionsForAdmin());
        $meta["header_group"]["children"]["container_font_size_min"]["children"]["font_size_min"]["arguments"]
        ["data"] = $this->buildSelectChildren("Minimal Font Size", "font_size_step", "3", $this->headerHeight->getAllOptionsForAdmin());
        $meta["header_group"]["children"]["container_font_size_step"]["children"]["font_size_step"]["arguments"]
        ["data"] = $this->buildSelectChildren("Font Size Step", "font_size_step", "3", $this->headerHeight->getAllOptionsForAdmin());
        $meta["header_group"]["children"]["container_font_size_default"]["children"]["font_size_default"]["arguments"]
        ["data"] = $this->buildSelectChildren("Default Font Size", "font_size_default", "3", $this->headerHeight->getAllOptionsForAdmin());
        return $meta;
    }

    public function buildSelectChildren($label, $code, $sortOrder, $options)
    {
        return [
            'config' => [
                'dataType' => 'select',
                'formElement' => 'select',
                'label' => __($label),
                'visible' => '1',
                'required' => '0',
                'notice' => null,
                'default' => null,
                "scopeLabel" => __("[STORE VIEW]"),
                "globalScope" => false,
                "source" => "header_information",
                'code' => $code,
                'sortOrder' => $sortOrder,
                'options' => $options,
                'componentType' => 'field',
            ],
        ];
    }

    public function buildContainer($label, $sortOrder)
    {
        return [
            'config' => [
                'formElement' => 'container',
                'label' => $label,
                'breakLine' => false,
                'componentType' => 'container',
                'sortOrder' => $sortOrder,
                'required' => "0"
            ],
        ];
    }

    /**
     * This function changes the visibility of label tab to false.
     *
     * @param array $meta
     * @return array
     */
    public function modifyLabelDataAttributes($meta)
    {
        $meta["label_group"]["arguments"]["data"]["config"]["componentType"] = "fieldset";
        $meta["label_group"]["arguments"]["data"]["config"]["label"] = __("Labels Information");
        $meta["label_group"]["arguments"]["data"]["config"]["collapsible"] = false;
        $meta["label_group"]["arguments"]["data"]["config"]["level"] = 1;
        $meta["label_group"]["arguments"]["data"]["config"]["visible"] = false;
        $meta["label_group"]["children"]["label_container"]["children"]
        ["html_content"]["arguments"]["block"] = $this->labelBlock;
        $meta["label_group"]["children"]["label_container"]["children"]
        ["html_content"]["arguments"]["data"]["config"]["componentType"] = "htmlContent";
        $meta["label_group"]["children"]["label_container"]["arguments"]
        ["data"]["config"]["sortOrder"] = "0";
        $meta["label_group"]["children"]["label_container"]["arguments"]
        ["data"]["config"]["componentType"] = "container";
        //set children

        $meta["label_group"]["children"]["container_label_position"]["arguments"]
        ["data"] = $this->buildContainer("Layout", 0);
        $meta["label_group"]["children"]["container_label_height"]["arguments"]["data"]
            = $this->buildContainer("Layout", 1);
        $meta["label_group"]["children"]["container_label_width"]["arguments"]["data"]
            = $this->buildContainer("Layout", 2);
        $meta["label_group"]["children"]["container_label_font_size_min"]["arguments"]["data"]
            = $this->buildContainer("Layout", 3);
        $meta["label_group"]["children"]["container_label_font_size_step"]["arguments"]["data"]
            = $this->buildContainer("Layout", 4);
        $meta["label_group"]["children"]["container_label_font_size_default"]["arguments"]["data"]
            = $this->buildContainer("Layout", 5);

        $meta["label_group"]["children"]["container_label_position"]["children"]["label_position"]["arguments"]
        ["data"] = $this->buildSelectChildren("Label Position", "label_position", "0", $this->headerPosition->getAllOptions());
        $meta["label_group"]["children"]["container_label_height"]["children"]["label_height"]["arguments"]
        ["data"] = $this->buildSelectChildren("Label Height", "label_height", "1", $this->headerHeight->getAllOptionsForAdmin());
        $meta["label_group"]["children"]["container_label_height"]["children"]["label_width"]["arguments"]
        ["data"] = $this->buildSelectChildren("Label Width", "label_width", "2", $this->headerHeight->getAllOptionsForAdmin());
        $meta["label_group"]["children"]["container_label_font_size_min"]["children"]["label_font_size_min"]["arguments"]
        ["data"] = $this->buildSelectChildren("Minimal Font Size", "label_font_size_min", "3", $this->headerHeight->getAllOptionsForAdmin());
        $meta["label_group"]["children"]["container_label_font_size_step"]["children"]["label_font_size_step"]["arguments"]
        ["data"] = $this->buildSelectChildren("Font Size Step", "label_font_size_step", "3", $this->headerHeight->getAllOptionsForAdmin());
        $meta["label_group"]["children"]["container_label_font_size_default"]["children"]["label_font_size_default"]["arguments"]
        ["data"] = $this->buildSelectChildren("Default Font Size", "label_font_size_default", "3", $this->headerHeight->getAllOptionsForAdmin());
        return $meta;
    }

    /**
     * This function changes the product listing of primary products section.
     *
     * @param array $meta
     * @return array
     */
    public function changeListingForm($meta)
    {
        if (isset($meta["bundle-items"]["children"]["modal"]
            ["arguments"]["data"]["config"]["options"]["buttons"]
            [1]["actions"][0]["targetName"])) {
            $meta["bundle-items"]["children"]["modal"]
            ["arguments"]["data"]["config"]["options"]["buttons"]
            [1]["actions"][0]["targetName"] = "index = custom_frame_product_listing";
        }
        if (isset($meta["bundle-items"]["children"]["modal"]
            ["children"]["bundle_product_listing"])) {
            $listingData["custom_frame_product_listing"] = $meta["bundle-items"]["children"]
            ["modal"]["children"]["bundle_product_listing"];
            $listingData["custom_frame_product_listing"]
            ["arguments"]["data"]["config"]["dataScope"] = "custom_frame_product_listing";
            $listingData["custom_frame_product_listing"]
            ["arguments"]["data"]["config"]["externalProvider"] =
                "custom_frame_product_listing.custom_frame_product_listing_data_source";
            $listingData["custom_frame_product_listing"]
            ["arguments"]["data"]["config"]["selectionsProvider"] =
                "custom_frame_product_listing.custom_frame_product_listing.product_columns.ids";
            $listingData["custom_frame_product_listing"]["arguments"]
            ["data"]["config"]["ns"] = "custom_frame_product_listing";
            $meta["bundle-items"]["children"]["modal"]["children"] = $listingData;
            $meta["bundle-items"]["children"]["bundle_options"]
            ["children"]["record"]["children"]["product_bundle_container"]
            ["children"]["modal_set"]["arguments"]["data"]["config"]
            ["listingDataProvider"] = "custom_frame_product_listing";
            $meta["bundle-items"]["children"]["bundle_options"]
            ["children"]["record"]["children"]["product_bundle_container"]
            ["children"]["modal_set"]["arguments"]["data"]["config"]
            ["actions"][1]["targetName"] =
                "product_form.product_form.bundle-items.modal.custom_frame_product_listing";
        }
        return $meta;
    }

    /**
     * This function provides the list of bundle options title.
     *
     * @return array[]
     */
    public function bundleOptions()
    {
        return [
            [
                'value' => 'Accessories',
                'label' => __('Accessories'),
            ],
            [
                'value' => 'Addons',
                'label' => __('Addons'),
            ],
            [
                'value' => 'Applied Rules',
                'label' => __('Applied Rules'),
            ],
            [
                'value' => 'Backing Board',
                'label' => __('Backing Board'),
            ],
            [
                'value' => 'Bottom Mat',
                'label' => __('Bottom Mat'),
            ],
            [
                'value' => 'Chalkboards',
                'label' => __('Chalkboards'),
            ],
            [
                'value' => 'Corkboards',
                'label' => __('Corkboards'),
            ],
            [
                'value' => 'Dryerase Board',
                'label' => __('Dryerase Board'),
            ],
            [
                'value' => 'Fabric',
                'label' => __('Fabric'),
            ],
            [
                'value' => 'Frame',
                'label' => __('Frame'),
            ],
            [
                'value' => 'Glass',
                'label' => __('Glass'),
            ],
            [
                'value' => 'Header',
                'label' => __('Header'),
            ],
            [
                'value' => 'Label',
                'label' => __('Label'),
            ],
            [
                'value' => 'Laminate Exterior',
                'label' => __('Laminate Exterior'),
            ],
            [
                'value' => 'Laminate Interior',
                'label' => __('Laminate Interior'),
            ],
            [
                'value' => 'Letter Board',
                'label' => __('Letter Board'),
            ],
            [
                'value' => 'Middle Mat',
                'label' => __('Middle Mat'),
            ],
            [
                'value' => 'Opening',
                'label' => __('Opening'),
            ],
            [
                'value' => 'Post Finish',
                'label' => __('Post Finish'),
            ],
            [
                'value' => 'Shelves',
                'label' => __('Shelves'),
            ],
            [
                'value' => 'Top Mat',
                'label' => __('Top Mat'),
            ]
        ];
    }
}
