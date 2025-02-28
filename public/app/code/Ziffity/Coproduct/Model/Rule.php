<?php
declare(strict_types=1);

namespace Ziffity\Coproduct\Model;

use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResourceModel;
use Magento\CatalogRule\Model\Rule\Action\CollectionFactory as RuleCollectionFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Ziffity\Coproduct\Model\Rule\Condition\CombineFactory;

/**
 * Catalog Rule data model
 */
class Rule extends \Magento\Rule\Model\AbstractModel
{

    /**
     * @var CombineFactory
     */
    protected $combineFactory;

    /**
     * @var RuleCollectionFactory
     */
    protected $actionCollectionFactory;

    /**
     * Rule constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param CombineFactory $combineFactory
     * @param RuleCollectionFactory $actionCollectionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     * @param Json $serializer
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context                       $context,
        Registry                      $registry,
        FormFactory                   $formFactory,
        TimezoneInterface             $localeDate,
        CombineFactory                $combineFactory,
        RuleCollectionFactory         $actionCollectionFactory,
        AbstractResource              $resource = null,
        AbstractDb                    $resourceCollection = null,
        array                         $data = [],
        ExtensionAttributesFactory    $extensionFactory = null,
        AttributeValueFactory         $customAttributeFactory = null,
        Json                          $serializer = null
    )
    {
        $this->combineFactory = $combineFactory;
        $this->actionCollectionFactory = $actionCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data,
            $extensionFactory,
            $customAttributeFactory,
            $serializer
        );
    }

    /**
     * Init resource model and id field
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(RuleResourceModel::class);
    }

    /**
     * Getter for rule conditions collection
     *
     * @return \Ziffity\Coproduct\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * Getter for rule actions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->actionCollectionFactory->create();
    }

    /**
     * Getter for conditions field set ID
     *
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }
}
