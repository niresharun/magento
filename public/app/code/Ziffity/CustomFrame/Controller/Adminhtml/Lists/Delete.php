<?php
namespace Ziffity\CustomFrame\Controller\Adminhtml\Lists;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Ziffity\CustomFrame\Model\QuantityClassificationFactory;
use Psr\Log\LoggerInterface;

class Delete extends Action
{

    /**
     * @var QuantityClassificationFactory
     */
    protected $listFactory;

    /**
     * @var Logger
     */
    protected $logger;
    
    /**
     * @param Context $context
     * @param QuantityClassificationFactory $listFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        QuantityClassificationFactory $listFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->listFactory = $listFactory;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->listFactory->create()->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('The list has been deleted !'));
                return $resultRedirect->setPath('*/index/');
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->messageManager->addErrorMessage(__('Unable to delete the list'));
                return $resultRedirect->setPath('*/lists/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a list to delete.'));

        return $resultRedirect->setPath('*/index/');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ziffity_CustomFrame::delete');
    }
}