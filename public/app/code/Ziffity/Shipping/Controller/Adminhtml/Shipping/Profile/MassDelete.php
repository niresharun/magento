<?php

namespace Ziffity\Shipping\Controller\Adminhtml\Shipping\Profile;

use Ziffity\Shipping\Model\ShippingProfile\ResourceModel\ShippingProfile\CollectionFactory as Collection;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Ziffity\Shipping\Helper\Data;

class MassDelete extends Action
{

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Collection
     */
    protected Collection $collection;

    /**
     * @var Filter
     */
    protected Filter $filter;

    /**
     * @param Context $context
     * @param Collection $collection
     * @param Filter $filter
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Collection $collection,
        Filter $filter,
        Data $helper
    ) {
        $this->collection = $collection;
        $this->filter = $filter;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Class MassDelete Execute function.
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        try {
            $found = false;
            $collection = $this->filter->getCollection($this->collection->create());
            $collectionSize = 0;
            foreach ($collection as $item) {
                if (empty($this->helper
                    ->isAllowedToDelete('shipping_profile', $item))) {
                    $found = true;
                    $item->delete();
                    $collectionSize++;
                }
            }
            if ($found) {
                $this->messageManager
                    ->addSuccessMessage(
                        __('A total of %1 element(s) have been deleted.', $collectionSize)
                    );
            }
        } catch (\Exception $exception) {
            $this->messageManager
                ->addErrorMessage($exception->getMessage());
        }
        return $this->resultRedirectFactory
            ->create()
            ->setPath('*/*/grid');
    }
}
