<?php

namespace Ziffity\CustomFrame\Block\Adminhtml\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Ziffity\CustomFrame\Helper\Data as Helper;

class Opening extends \Magento\Backend\Block\Template
{
    /**
     * Block template.
     *
     * @var string
     */
    protected $_template = 'opening.phtml';

    /**
     * @var string
     */
    protected $_nameInLayout = 'opening_group';

    /**
     * @var Registry
     */
    public $registry;

    /**
     * @var Helper
     */
    public $helper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Registry $registry,
        Helper $helper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * This function loads the product json from the helper.
     *
     * @return false|string
     * @throws NoSuchEntityException
     */
    public function getProductJson()
    {
        return $this->helper->loadProductJson($this);
    }

    /**
     * This function checks if this product has opening as option title.
     *
     * @return bool
     */
    public function loadWidget()
    {
        $sku = $this->registry->registry('current_product')->getSku();
        if ($sku!==null && !empty($sku)){
            return $this->helper->hasOpening($sku);
        }
        return false;
    }
}
