<?php

namespace Ziffity\CustomFrame\Model\CustomFrame;

use Ziffity\CustomFrame\Model\Option\SaveAction;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Ziffity\CustomFrame\Model\Product\Type;

/**
 * Repository for performing CRUD operations for a bundle product's options.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionRepository implements \Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface
{
    const OPTION_TYPE_PRIMARY_PRODUCTS = "primary";
    const OPTION_TYPE_CO_PRODUCTS = "Co-Products";

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Product\Type
     */
    protected $type;

    /**
     * @var \Magento\Bundle\Api\Data\OptionInterfaceFactory
     */
    protected $optionFactory;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Option
     */
    protected $optionResource;

    /**
     * @var \Magento\Bundle\Api\ProductLinkManagementInterface
     */
    protected $linkManagement;

    /**
     * @var Product\OptionList
     */
    protected $productOptionList;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var SaveAction
     */
    private $optionSave;

    public $pagination = null;

    public $searchQuery = null;

    public $filters = null;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param Product\Type $type
     * @param \Magento\Bundle\Api\Data\OptionInterfaceFactory $optionFactory
     * @param \Magento\Bundle\Model\ResourceModel\Option $optionResource
     * @param \Magento\Bundle\Api\ProductLinkManagementInterface $linkManagement
     * @param Product\OptionList $productOptionList
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param SaveAction $optionSave
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Bundle\Model\Product\Type $type,
        \Magento\Bundle\Api\Data\OptionInterfaceFactory $optionFactory,
        \Magento\Bundle\Model\ResourceModel\Option $optionResource,
        \Ziffity\CustomFrame\Api\ProductLinkManagementInterface $linkManagement,
        \Ziffity\CustomFrame\Model\Product\OptionList $productOptionList,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        SaveAction $optionSave
    ) {
        $this->productRepository = $productRepository;
        $this->type = $type;
        $this->optionFactory = $optionFactory;
        $this->optionResource = $optionResource;
        $this->linkManagement = $linkManagement;
        $this->productOptionList = $productOptionList;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->optionSave = $optionSave;
    }

    /**
     * @inheritdoc
     */
    public function get($sku, $optionId)
    {
        $product = $this->getProduct($sku);

        /** @var \Magento\Bundle\Model\Option $option */
        $option = $this->type->getOptionsCollection($product)->getItemById($optionId);
        if (!$option || !$option->getId()) {
            throw new NoSuchEntityException(
                __("The option that was requested doesn't exist. Verify the entity and try again.")
            );
        }

        $productLinks = $this->linkManagement->getChildren($product->getSku(), $optionId);

        /** @var \Magento\Bundle\Api\Data\OptionInterface $optionDataObject */
        $optionDataObject = $this->optionFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $optionDataObject,
            $option->getData(),
            \Magento\Bundle\Api\Data\OptionInterface::class
        );

        $optionDataObject->setOptionId($option->getId());
        $optionDataObject->setTitle($option->getTitle() === null ? $option->getDefaultTitle() : $option->getTitle());
        $optionDataObject->setSku($product->getSku());
        $optionDataObject->setProductLinks($productLinks);

        return $optionDataObject;
    }

    /**
     * @inheritdoc
     */
    public function getList($sku, $filter = null,$optionTitle = null, $activeItem = null)
    {
        $product = $this->getProduct($sku);
        return $this->getListByProduct($product, $filter, $optionTitle, $activeItem);
    }

    /**
     * Return list of product options
     *
     * @param ProductInterface $product
     * @return \Magento\Bundle\Api\Data\OptionInterface[]
     */
    public function getListByProduct(ProductInterface $product, $filter = null, $optionTitle = null, $activeItem = null)
    {
        $primaryProducts = [];
        $coProducts = [];
        $items = $this->productOptionList->getItems($product, $filter,
            $optionTitle, $this->pagination, $this->searchQuery, $this->filters);
        switch($filter) {
            case self::OPTION_TYPE_PRIMARY_PRODUCTS:
                foreach ($items as $item) {
                    if ($item->getTitle() != self::OPTION_TYPE_CO_PRODUCTS) {
                        array_push($primaryProducts, $item);
                    }
                }
                return $primaryProducts;
                break;
            case self::OPTION_TYPE_CO_PRODUCTS:
                foreach ($items as $item) {
                    if ($item->getTitle() == self::OPTION_TYPE_CO_PRODUCTS) {
                        array_push($coProducts, $item);
                    }
                }
                return $coProducts;
                break;
            default:
                return $items;
        }
    }

    /**
     * @inheritdoc
     */
    public function delete(\Magento\Bundle\Api\Data\OptionInterface $option)
    {
        try {
            $this->optionResource->delete($option);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\StateException(
                __('The option with "%1" ID can\'t be deleted.', $option->getOptionId()),
                $exception
            );
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($sku, $optionId)
    {
        /** @var \Magento\Bundle\Api\Data\OptionInterface $option */
        $option = $this->get($sku, $optionId);
        $hasBeenDeleted = $this->delete($option);

        return $hasBeenDeleted;
    }

    /**
     * @inheritdoc
     */
    public function save(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Magento\Bundle\Api\Data\OptionInterface $option
    ) {
        $savedOption = $this->optionSave->save($product, $option);

        $productToSave = $this->productRepository->get($product->getSku());
        $this->productRepository->save($productToSave);

        return $savedOption->getOptionId();
    }

    /**
     * Update option selections
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Bundle\Api\Data\OptionInterface $option
     * @return $this
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function updateOptionSelection(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Magento\Bundle\Api\Data\OptionInterface $option
    ) {
        $optionId = $option->getOptionId();
        $existingLinks = $this->linkManagement->getChildren($product->getSku(), $optionId);
        $linksToAdd = [];
        $linksToUpdate = [];
        $linksToDelete = [];
        if (is_array($option->getProductLinks())) {
            $productLinks = $option->getProductLinks();
            foreach ($productLinks as $productLink) {
                if (!$productLink->getId() && !$productLink->getSelectionId()) {
                    $linksToAdd[] = $productLink;
                } else {
                    $linksToUpdate[] = $productLink;
                }
            }
            /** @var \Magento\Bundle\Api\Data\LinkInterface[] $linksToDelete */
            $linksToDelete = $this->compareLinks($existingLinks, $linksToUpdate);
        }
        foreach ($linksToUpdate as $linkedProduct) {
            $this->linkManagement->saveChild($product->getSku(), $linkedProduct);
        }
        foreach ($linksToDelete as $linkedProduct) {
            $this->linkManagement->removeChild(
                $product->getSku(),
                $option->getOptionId(),
                $linkedProduct->getSku()
            );
        }
        foreach ($linksToAdd as $linkedProduct) {
            $this->linkManagement->addChild($product, $option->getOptionId(), $linkedProduct);
        }
        return $this;
    }

    /**
     * Retrieve product by SKU
     *
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function getProduct($sku)
    {
        $product = $this->productRepository->get($sku, false, null, true);
        if ($product->getTypeId() !== Type::TYPE_CODE) {
            throw new InputException(__('This is implemented for customframe products only.'));
        }
        return $product;
    }

    /**
     * Computes the difference between given arrays.
     *
     * @param \Magento\Bundle\Api\Data\LinkInterface[] $firstArray
     * @param \Magento\Bundle\Api\Data\LinkInterface[] $secondArray
     *
     * @return array
     */
    private function compareLinks(array $firstArray, array $secondArray)
    {
        $result = [];

        $firstArrayIds = [];
        $firstArrayMap = [];

        $secondArrayIds = [];

        foreach ($firstArray as $item) {
            $firstArrayIds[] = $item->getId();

            $firstArrayMap[$item->getId()] = $item;
        }

        foreach ($secondArray as $item) {
            $secondArrayIds[] = $item->getId();
        }

        foreach (array_diff($firstArrayIds, $secondArrayIds) as $id) {
            $result[] = $firstArrayMap[$id];
        }

        return $result;
    }
}
