<?php

namespace Ziffity\ProductCustomizer\Model;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverInterface;
use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Ziffity\ProductCustomizer\Model\SearchCriteriaResolverFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Search\Request\EmptyRequestDataException;
use Magento\Framework\Search\Request\NonExistingRequestNameException;
use Magento\Framework\Api\Search\SearchResultFactory;
use Psr\Log\LoggerInterface as Logger;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as EavCollection;

class IndexerCollectionFetchData
{

    /**
     * @var EavCollection
     */
    protected $eavCollection;

    public const SORT_ORDER_DESC = 'DESC';

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory;

    /**
     * @var SearchCriteriaResolverFactory
     */
    private $searchCriteriaResolverFactory;

    private $searchRequestName = 'catalog_view_container';

    /**
     * @var
     */
    protected $search;

    /**
     * @var array
     */
    private $searchOrders = '{"relevance":"DESC","entity_id":"DESC"}';

    /**
     * Current page number for items pager
     *
     * @var int
     */
    protected $_curPage = 1;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var
     */
    public $searchResult;

    public $_totalRecords;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    protected $attributeRepository;

    /**
     * @param FilterBuilder $filterBuilder
     * @param Logger $logger
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SearchCriteriaResolverFactory $searchCriteriaResolverFactory
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(
    FilterBuilder $filterBuilder,
    Logger $logger,
    SearchCriteriaBuilder $searchCriteriaBuilder,
    SearchCriteriaResolverFactory $searchCriteriaResolverFactory,
    SearchResultFactory $searchResultFactory,
    EavCollection $eavCollection,Config $attributeRepository){
        $this->filterBuilder = $filterBuilder;
        $this->_logger = $logger;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchCriteriaResolverFactory = $searchCriteriaResolverFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->eavCollection = $eavCollection;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param array $sku
     * @return array
     * @throws LocalizedException
     * @throws StateException
     */
    public function renderFilters($sku)
    {
        $result = [];
        $this->prepareSkuSearchTerm($sku);
        $searchCriteria = $this->getSearchCriteriaResolver()->resolve();
        try {
            $this->searchResult =  $this->getSearch()->search($searchCriteria);
        } catch (EmptyRequestDataException $e) {
            $this->searchResult = $this->createEmptyResult();
        } catch (NonExistingRequestNameException $e) {
            $this->_logger->error($e->getMessage());
            throw new LocalizedException(__('An error occurred. For details, see the error log.'));
        }
        if (!empty($this->searchResult->getItems())) {
            foreach ($this->searchResult->getAggregations() as $key => $aggregation) {
                $attributeCode = explode("_bucket", $aggregation->getName())[0];
                $data = $this
                    ->getFacetedData($attributeCode);
                if (!empty($data)) {
                    $result[$attributeCode] = $data;
                }
            }
        }
        return $this->processFilters($result);
    }

    /**
     * Return field faceted data from faceted search result
     *
     * @param string $field
     * @return array
     * @throws StateException
     */
    public function getFacetedData($field)
    {
        $result = [];
        $aggregations = $this->searchResult->getAggregations();
        // This behavior is for case with empty object when we got EmptyRequestDataException
        if (null !== $aggregations) {
            $bucket = $aggregations->getBucket($field . RequestGenerator::BUCKET_SUFFIX);
            if ($bucket) {
                foreach ($bucket->getValues() as $value) {
                    $metrics = $value->getMetrics();
                    $result[$metrics['value']] = $metrics;
                }
            } else {
                throw new StateException(__("The bucket doesn't exist."));
            }
        }
        return $result;
    }

    /**
     * Get search.
     *
     * @deprecated 100.1.0
     * @return \Magento\Search\Api\SearchInterface
     */
    private function getSearch()
    {
        if ($this->search === null) {
            $this->search = ObjectManager::getInstance()->get(\Magento\Search\Api\SearchInterface::class);
        }
        return $this->search;
    }

    /**
     * Create empty search result
     *
     * @return SearchResultInterface
     */
    private function createEmptyResult()
    {
        return $this->searchResultFactory->create()->setItems([]);
    }

    /**
     * Get search criteria resolver.
     *
     * @return SearchCriteriaResolverInterface
     */
    private function getSearchCriteriaResolver(): SearchCriteriaResolverInterface
    {
        return $this->searchCriteriaResolverFactory->create(
            [
                'builder' => $this->getSearchCriteriaBuilder(),
                'collection' => $this,
                'searchRequestName' => $this->searchRequestName,
                'currentPage' => (int)$this->_curPage,
                'size' => $this->getPageSize(),
                'orders' => json_decode($this->searchOrders,true),
            ]
        );
    }

    public function getPageSize()
    {
        //TODO: Have to bring the configuration for this to get the page size for this query, will do it later scope of this task
        return 12;
    }

    /**
     * Set search criteria builder.
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Api\Search\SearchCriteriaBuilder
     */
    private function getSearchCriteriaBuilder()
    {
        if ($this->searchCriteriaBuilder === null) {
            $this->searchCriteriaBuilder = ObjectManager::getInstance()
                ->get(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class);
        }
        return $this->searchCriteriaBuilder;
    }

    /**
     * Set product visibility filter for enabled products
     *
     * @param array $visibility
     * @return void
     */
    public function prepareSkuSearchTerm($sku)
    {
        $this->filterBuilder->setField('sku');
        $this->filterBuilder->setValue($sku);
        $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
    }

    public function processFilters($filters)
    {
        $result = [];
        foreach ($filters as $attributeCode=>$filter)
        {
            if ($attributeCode!=="price") {
                $collection = $this->eavCollection->create()
                    ->addFieldToFilter('attribute_code',$attributeCode);
                if (count($collection)) {
                    $label = $collection->getFirstItem()->getFrontendLabel();
                    $iteration = 0;
                    if ($iteration == 0){
                        $result[$attributeCode][] = $this->addAllOptionInFilter(
                            $this->convertFilterParam($attributeCode,null),$label);
                        $iteration++;
                    }
                    foreach ($filter as $item=>$value) {
                        $result[$attributeCode][] = [
                            'label' => $this->getOptionLabelFromOptionId($attributeCode,$item),
                            'frontend_label'=>$label,
                            'count' => $value['count'],
                            'id' => $item,
                            'attribute_code' => $attributeCode,
                            'all_filter' => false,
                            'filter_param' => $this->convertFilterParam($attributeCode,$item)];
                    }
                }
            }
        }
        return $result;
    }

    public function addAllOptionInFilter($filterParam,$label): array
    {
        return [
            'label' => 'All',
            'frontend_label'=>$label,
            'count' => null,
            'id' => null,
            'attribute_code' => null,
            'filter_param' => $filterParam,
            'all_filter' => true
        ];
    }

    public function convertFilterParam($attributeCode,$id)
    {
        return json_encode([
            'id'=>$id,
            'attribute_code'=>$attributeCode
        ]);
    }

    public function getOptionLabelFromOptionId($attributeCode,$optionId)
    {
        $collection = $this->attributeRepository->getAttribute(\Magento\Catalog\Model\Product::ENTITY,$attributeCode);
        return $collection->getSource()->getOptionText($optionId);
    }

    /**
     * This function gets the sku of all the child products for fetching filters.
     *
     * @param ConfigProviderInterface $class
     * @param string $type
     * @return array
     */
    public function getAllVisibleProductSku($class, $type , $skuToFilter = [])
    {
        $result = [];
        $class->setPagination(null);
        $visibleProducts = $class->optionsRepository
            ->getList($class->getSku(), "primary", $type);
        foreach ($visibleProducts as $value) {
            if ($value->getTitle() == $type) {
                if (!empty($skuToFilter)) {
                    foreach ($value->getProductLinks() as $product) {
                        if (in_array($product->getSku(), $skuToFilter)) {
                            $result[] = $product->getSku();
                        }
                    }
                } elseif (empty($skuToFilter)) {
                    foreach ($value->getProductLinks() as $product) {
                        $result[] = $product->getSku();
                    }
                }
            }
        }
        return $result;
    }

}
