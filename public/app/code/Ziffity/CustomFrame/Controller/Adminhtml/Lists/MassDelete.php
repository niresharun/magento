<?php
namespace Ziffity\CustomFrame\Controller\Adminhtml\Lists;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Ziffity\CustomFrame\Model\ResourceModel\QuantityClassification\CollectionFactory;

class MassDelete extends Action
{
    /**
     * @var CollectionFactory 
     */
    protected $collectionFactory;

    /**
     * @var Filter
     */
    protected $filter;
    
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     *  @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $model) {
            $model->delete();
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 list(s) have been deleted.', $collectionSize));

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/index/');
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ziffity_CustomFrame::delete');
    }
}