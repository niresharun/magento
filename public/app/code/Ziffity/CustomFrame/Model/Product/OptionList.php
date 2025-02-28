<?php

namespace Ziffity\CustomFrame\Model\Product;

class OptionList
{
    const OPTION_TYPE_PRIMARY_PRODUCTS = "primary";
    const OPTION_TYPE_CO_PRODUCTS = "Co-Products";

    /**
     * @var Ziffity\CustomFrame\Api\Data\OptionInterfaceFactory
     */
    protected $optionFactory;

    /**
     * @var \Ziffity\CustomFrame\Model\Product\Type
     */
    protected $type;

    /**
     * @var LinksList
     */
    protected $linkList;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @param Type $type
     * @param \Magento\Bundle\Api\Data\OptionInterfaceFactory $optionFactory
     * @param LinksList $linkList
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        \Ziffity\CustomFrame\Model\Product\Type $type,
        \Magento\Bundle\Api\Data\OptionInterfaceFactory $optionFactory,
        \Ziffity\CustomFrame\Model\Product\LinksList $linkList,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->type = $type;
        $this->optionFactory = $optionFactory;
        $this->linkList = $linkList;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Bundle\Api\Data\OptionInterface[]
     */
    public function getItems(\Magento\Catalog\Api\Data\ProductInterface $product,
                             $filter = null, $optionTitle = null, $pagination = null,
                             $searchQuery = null, $filters = null)
    {
        $optionCollection = $this->type->getOptionsCollection($product, $optionTitle);
        $this->extensionAttributesJoinProcessor->process($optionCollection);

        $optionList = [];
        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($optionCollection as $option) {
            $productLinks = $this->linkList->getItems($product, $option->getOptionId(), $optionTitle, $pagination, $searchQuery, $filters);
            /** @var \Magento\Bundle\Api\Data\OptionInterface $optionDataObject */
            $optionDataObject = $this->optionFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $optionDataObject,
                $option->getData(),
                \Magento\Bundle\Api\Data\OptionInterface::class
            );
            $optionDataObject->setOptionId($option->getOptionId())
                ->setTitle($option->getTitle() === null ? $option->getDefaultTitle() : $option->getTitle())
                ->setDefaultTitle($option->getDefaultTitle())
                ->setSku($product->getSku())
                ->setProductLinks($productLinks);
            $optionList[] = $optionDataObject;
        }
        return $optionList;
    }
}
