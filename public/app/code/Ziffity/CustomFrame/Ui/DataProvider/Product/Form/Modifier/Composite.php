<?php

namespace Ziffity\CustomFrame\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\ObjectManagerInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Ziffity\CustomFrame\Model\Product\Type;
use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ziffity\CustomFrame\Ui\DataProvider\Product\Form\Modifier\CoProductsPanel;
use Magento\Bundle\Model\SelectionFactory;
use Magento\Bundle\Model\ResourceModel\Selection;

/**
 * Class Bundle customizes Bundle product creation flow
 */
class Composite extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var array
     */
    protected $modifiers = [];

    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ProductOptionRepositoryInterface
     */
    protected $optionsRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var SelectionFactory
     */
    protected $selectionFactory;

    /**
     * @var SelectionResource
     */
    protected $selectionResource;

    /**
     * @param LocatorInterface $locator
     * @param ObjectManagerInterface $objectManager
     * @param ProductOptionRepositoryInterface $optionsRepository
     * @param ProductRepositoryInterface $productRepository
     * @param SelectionFactory $selectionFactory
     * @param Selection $selectionResource
     * @param array $modifiers
     */
    public function __construct(
        LocatorInterface $locator,
        ObjectManagerInterface $objectManager,
        ProductOptionRepositoryInterface $optionsRepository,
        ProductRepositoryInterface $productRepository,
        SelectionFactory $selectionFactory,
        Selection $selectionResource,
        array $modifiers = []
    ) {
        $this->locator = $locator;
        $this->objectManager = $objectManager;
        $this->optionsRepository = $optionsRepository;
        $this->productRepository = $productRepository;
        $this->selectionFactory = $selectionFactory;
        $this->selectionResource = $selectionResource;
        $this->modifiers = $modifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if ($this->locator->getProduct()->getTypeId() === Type::TYPE_CODE) {
            foreach ($this->modifiers as $bundleClass) {
                /** @var ModifierInterface $bundleModifier */
                $bundleModifier = $this->objectManager->get($bundleClass);
                if (!$bundleModifier instanceof ModifierInterface) {
                    throw new \InvalidArgumentException(
                        'Type "' . $bundleClass . '" is not an instance of ' . ModifierInterface::class
                    );
                }
                $meta = $bundleModifier->modifyMeta($meta);
            }
        }
        return $meta;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function modifyData(array $data)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $this->locator->getProduct();
        $modelId = $product->getId();
        $isBundleProduct = $product->getTypeId() === Type::TYPE_CODE;
        if ($isBundleProduct && $modelId) {
            $data[$modelId][CoProductsPanel::CODE_COPRODUCTS_OPTIONS][CoProductsPanel::CODE_COPRODUCTS_OPTIONS] = [];
            /** @var \Magento\Bundle\Api\Data\OptionInterface $option */
            foreach ($this->optionsRepository->getList($product->getSku()) as $option) {
                $selections = [];
                /** @var \Magento\Bundle\Api\Data\LinkInterface $productLink */
                foreach ($option->getProductLinks() as $productLink) {
                    $linkedProduct = $this->productRepository->get($productLink->getSku());
                    $integerQty = 1;
                    if ($linkedProduct->getExtensionAttributes()->getStockItem()) {
                        if ($linkedProduct->getExtensionAttributes()->getStockItem()->getIsQtyDecimal()) {
                            $integerQty = 0;
                        }
                    }
                    $selection = $this->selectionFactory->create();
                    $this->selectionResource->load($selection, $productLink->getId());
                    $selections[] = [
                        'selection_id' => $productLink->getId(),
                        'option_id' => $productLink->getOptionId(),
                        'product_id' => $linkedProduct->getId(),
                        'name' => $linkedProduct->getName(),
                        'sku' => $linkedProduct->getSku(),
                        'is_default' => ($productLink->getIsDefault()) ? '1' : '0',
                        'selection_price_value' => $productLink->getPrice(),
                        'selection_price_type' => $productLink->getPriceType(),
                        'selection_qty' => $integerQty ? (int)$productLink->getQty() : $productLink->getQty(),
                        'selection_can_change_qty' => $productLink->getCanChangeQuantity(),
                        'selection_qty_is_integer' => (bool)$integerQty,
                        'position' => $productLink->getPosition(),
                        'product_quantity_classification' => $selection->getProductQuantityClassification(),
                        'product_quantity_calculation' => $selection->getProductQuantityCalculation(),
                        'delete' => '',
                    ];
                }

                $optionsData = [
                    'position' => $option->getPosition(),
                    'option_id' => $option->getOptionId(),
                    'title' => $option->getTitle(),
                    'default_title' => $option->getDefaultTitle(),
                    'type' => $option->getType(),
                    'required' => ($option->getRequired()) ? '1' : '0',
                    'bundle_selections' => $selections,
                ];

                if($option->getTitle()=="Co-Products") {
                    $data[$modelId][CoProductsPanel::CODE_COPRODUCTS_OPTIONS][CoProductsPanel::CODE_COPRODUCTS_OPTIONS][] = $optionsData;
                } else {
                    $data[$modelId][BundlePanel::CODE_BUNDLE_OPTIONS][BundlePanel::CODE_BUNDLE_OPTIONS][] = $optionsData;
                }
            }
        }

        return $data;
    }
}
