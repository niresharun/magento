<?php
declare(strict_types=1);

namespace Ziffity\Coproduct\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Ziffity\Coproduct\Model\RuleFactory;
use Ziffity\ProductCustomizer\Model\Components\Measurements\FrameSize;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Bundle\Model\Product\Type;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Ziffity\Coproduct\Model\Evaluate;

/**
 * Catalog Rule data model
 */
class ProcessRule
{
    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param RuleFactory $ruleFactory
     * @param FrameSize $frameSize
     * @param ProductRepositoryInterface $productRepository
     * @param Type $type
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param LoggerInterface $logger
     * @param Evaluate $evaluate
     */
    public function __construct(
        private ProductCollectionFactory $productCollectionFactory,
        private RuleFactory $ruleFactory,
        private FrameSize $frameSize,
        private ProductRepositoryInterface $productRepository,
        private Type $type,
        private AttributeSetRepositoryInterface $attributeSetRepository,
        private LoggerInterface $logger,
        private Evaluate $evaluate
    ) {
    }

    /**
     * Return Co product's calculated price
     *
     * @param \Magento\Catalog\Model\Product $parentProduct
     * @param [] $selectionData
     * @return \Magento\Catalog\Model\Product
     */
    public function apply($parentProduct, $selectionData)
    {
        $coproducts = [];
        $coproductDetails = [];
        try {
            $assignedCoproducts = $this->getAssociatedCoproductFromOptions($parentProduct);
            if ($assignedCoproducts) {
                $parentProduct = $this->prepareRuleValues($parentProduct, $selectionData);
                foreach ($assignedCoproducts as $coproduct) {
                    $rule = $this->ruleFactory->create();
                    $rule->setData('conditions_serialized', $coproduct->getConditions());
                    $validate = $rule->getConditions()->validate($parentProduct);
                    if ($validate) {
                        $parentProduct->setCustomframePrice($coproduct->getCustomframePrice());
                        $parentProduct->setCustomframeSize($coproduct->getCustomframeSize());
                        $parentProduct->setCustomframeValue($coproduct->getCustomframeValue());
                        $parentProduct->setQty($coproduct->getQuantityRequired());
                        $evaluatedData = $this->evaluate->evaluateFormula($parentProduct);
                        $coproducts[$coproduct->getSku()] = $evaluatedData['price'];
                        $evaluatedData['qty'] = $coproduct->getQuantityRequired();
                        $coproductDetails[$coproduct->getSku()] = $evaluatedData;
                    }
                }
            }

        } catch (\Throwable $e) {
            $this->logger->critical('criticalProcessRule');
            $this->logger->critical("PRODUCT-SKU:".$parentProduct->getSku());
            $this->logger->critical($e);
        }
        $parentProduct->setCoProductPriceList($coproducts);
        $parentProduct->setCoProductDetails($coproductDetails);
        return $parentProduct;
    }

    /**
     * Return Co product's calculated price
     *
     * @param \Magento\Catalog\Model\Product $parentProduct
     * @param [] $selectionData
     * @return \Magento\Catalog\Model\Product $parentProduct
     */
    public function prepareRuleValues($parentProduct, $selectionData)
    {
        if (!empty($selectionData)) {
            $overallWidth = $this->frameSize->getOverallWidth($selectionData);
            $overallHeight = $this->frameSize->getOverallHeight($selectionData);
            $parentProduct->setOverallWidth($overallWidth);
            $parentProduct->setOverallHeight($overallHeight);
            $parentProduct->setOverallFrameWidth($overallWidth);
            $parentProduct->setOverallFrameHeight($overallHeight);
            $parentProduct->setInnerFrameWidth($this->frameSize->getInnerFrameWidth($selectionData));
            $parentProduct->setInnerFrameHeight($this->frameSize->getInnerFrameHeight($selectionData));
            $parentProduct->setBackOfMouldingWidth($this->frameSize->getBackOfMouldingWidth($selectionData));
            $customizerProducts = [];
            foreach ($selectionData as $component => $selection) {
                $commonComponents = ['frame' => 'frame', 'glass' => 'glass', 'letter_board' => 'letterboard', 'post_finish' => 'postfinish', 'cork_board' => 'corkboard', 'chalk_board' => 'chalkboard', 'dryerase_board' => 'dryeraseboard', 'fabric' => 'fabric', 'backing_board' => 'backinboard'];
//                $commonComponents = ['frame', 'glass', 'letterboard', 'postfinish', 'corkboard', 'chalkboard', 'dryeraseboard', 'fabric', 'backinboard'];
                if (in_array($component, array_keys($commonComponents))) {
                    if (isset($selection['active_item']['id'])) {
                        $customizerProducts[$commonComponents[$component]] = $this->productRepository->getById($selection['active_item']['id']);
                    }
                } elseif ($component = "mat") {
                    if (isset($selection['active_items'])) {
                        foreach ($selection['active_items'] as $matType => $selection) {
                            if (isset($selection['id'])) {
                                $customizerProducts[$matType."_mat"] = $this->productRepository->getById($selection['id']);
                            }
                        }
                    }
                } elseif ($component = "laminate_finish") {
                    if (isset($selection['active_items']['laminate_exterior']['id'])) {
                        $customizerProducts['laminate_exterior'] = $this->productRepository->getById($selection['active_items']['laminate_exterior']['id']);
                    }
                    if (isset($selection['active_items']['laminate_interior']['id'])) {
                        $customizerProducts['laminate_interior'] = $this->productRepository->getById($selection['active_items']['laminate_interior']['id']);
                    }
                }

            }
            $parentProduct->setData('customizer_products', $customizerProducts);
        }
        return $parentProduct;
    }

    /**
     * Get bundle product options
     *
     * @param ProductInterface|\Magento\Catalog\Model\Product $parentProduct
     * @return \Magento\Framework\DataObject[]
     */
    private function getAssociatedCoproductFromOptions(ProductInterface $parentProduct)
    {
        $selectionCollection = null;
        /** @var Type $productTypeInstance */
        $productTypeInstance = $parentProduct->getTypeInstance();
        $productTypeInstance->setStoreFilter(
            $parentProduct->getStoreId(),
            $parentProduct
        );
        $option = $this->type->getOptionsCollection($parentProduct)->getItemByColumnValue('default_title', \Ziffity\ProductCustomizer\Helper\Data::CO_PRODUCTS);
        if ($option) {
            $selectionCollection = $productTypeInstance->getSelectionsCollection(
                [$option['option_id']],
                $parentProduct
            );
        }
        if ($selectionCollection) {
            $selectionCollection->addAttributeToFilter('status', ['eq' => ProductStatus::STATUS_ENABLED]);
        }
        return $selectionCollection;
    }

    /**
     * Return Co product's calculated price
     * @TODO @raj Try to include all parameters from conditions selection or Alternate idea(specific condition show|use customframe)
     *
     * @param \Magento\Catalog\Model\Product $parentProduct
     * @param [] $selectionData
     * @param string $typeAttributeName
     * @return float
     */
    public function applyPrimary($product, $selectionData, $typeAttributeName)
    {
        try {
            $calculatedPrice = 0;
            $attributeSet = $this->attributeSetRepository->get($product->getAttributeSetId());
            $coproductCollection = $this->productCollectionFactory->create();
            $coproductCollection->addAttributeToSelect(['conditions', $typeAttributeName, 'customframe_price', 'customframe_size', 'customframe_value', 'quantity_required'])
                ->addAttributeToFilter('status', ['eq' => ProductStatus::STATUS_ENABLED])
                ->addFieldtoFilter('type_id', \Ziffity\Coproduct\Model\Product\Type\Coproduct::TYPE_CODE)
                ->addAttributeToFilter('applicable_to', $attributeSet->getAttributeSetName())
                ->addAttributeToFilter(
                    [
                        ['attribute' => $typeAttributeName,'null' => true ],
                        ['attribute' => $typeAttributeName,'eq' => $product->getData($typeAttributeName)]
                    ]
                );
            $coproductCollection->getSelect()->order($typeAttributeName.' desc');

            if ($coproductCollection->getSize()) {
                if (isset($selectionData['parent_product'])) {
                    $product->setFrameType($selectionData['parent_product']->getFrameType());
                }
                $overallWidth = $this->frameSize->getOverallWidth($selectionData);
                $overallHeight = $this->frameSize->getOverallHeight($selectionData);
                $product->setOverallWidth($overallWidth);
                $product->setOverallHeight($overallHeight);
                $product->setOverallFrameWidth($overallWidth);
                $product->setOverallFrameHeight($overallHeight);
                $product->setInnerFrameWidth($this->frameSize->getInnerFrameWidth($selectionData));
                $product->setInnerFrameHeight($this->frameSize->getInnerFrameHeight($selectionData));
                $product->setBackOfMouldingWidth($this->frameSize->getBackOfMouldingWidth($selectionData));
                foreach ($coproductCollection as $coproduct) {
                    $rule = $this->ruleFactory->create();
                    $rule->setData('conditions_serialized', $coproduct->getConditions());
                    $product->setData('non_customframe', 1);
                    $validate = $rule->getConditions()->validate($product);
                    if ($validate) {
                        $product->setCustomframePrice($coproduct->getCustomframePrice());
                        $product->setCustomframeSize($coproduct->getCustomframeSize());
                        $product->setCustomframeValue($coproduct->getCustomframeValue());
                        $product->setQty($coproduct->getQuantityRequired());
                        $evaluatedData = $this->evaluate->evaluateFormula($product);
                        $calculatedPrice = $evaluatedData['price'];
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $calculatedPrice;
    }
}
