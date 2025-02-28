<?php

namespace Ziffity\CustomFrame\Block\Adminhtml\QuantityClassification\Edit;

use Magento\Framework\UrlInterface;
use \Magento\Backend\Block\Widget\Context;
use \Magento\Framework\Registry;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var Context
     */
    protected $context;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Context $context,
        Registry $registry,
        UrlInterface $urlBuilder
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Return the current list Id.
     *
     * @return int|null
     */
    public function getListId()
    {
        $listId = $this->registry->registry('current_list_id');
        return $listId ? $listId : null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
