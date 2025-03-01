<?php

namespace Ziffity\CustomFrame\Model\Product;

use Ziffity\CustomFrame\Api\ProductOptionRepositoryInterface as OptionRepository;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * ReadHandler constructor.
     *
     * @param OptionRepository $optionRepository
     */
    public function __construct(OptionRepository $optionRepository)
    {
        $this->optionRepository = $optionRepository;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var $entity \Magento\Catalog\Api\Data\ProductInterface */
        if ($entity->getTypeId() != Type::TYPE_CODE) {
            return $entity;
        }
        $entityExtension = $entity->getExtensionAttributes();
        $options = $this->optionRepository->getListByProduct($entity);
        if ($options) {
            $entityExtension->setBundleProductOptions($options);
        }
        $entity->setExtensionAttributes($entityExtension);
        return $entity;
    }
}
