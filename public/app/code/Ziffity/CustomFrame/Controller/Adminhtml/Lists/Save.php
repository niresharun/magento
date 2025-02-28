<?php

namespace Ziffity\CustomFrame\Controller\Adminhtml\Lists;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Serialize\Serializer\Json;
use \Ziffity\CustomFrame\Model\QuantityClassificationFactory;
use Psr\Log\LoggerInterface;


class Save extends \Magento\Backend\App\Action
{
    /**
     * @var QuantityClassificationFactory
     */
    protected $listFactory;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Context $context
     * @param Json
     * @param QuantityClassificationFactory $listFactory
     */
    public function __construct(
        Context $context,
        Json $serializer,
        QuantityClassificationFactory $listFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->serializer = $serializer;
        $this->listFactory = $listFactory;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $resultRedirect->setPath('quantity_classification/index/');
        }
        try {
            $rowData = $this->listFactory->create();

            if (isset($data['quantity_classification']['id'])) {
               $rowData->load($data['quantity_classification']['id']);
            }
            $rowData->setListName($data['quantity_classification']['list_name']);
            $rowData->setIdentifier($data['quantity_classification']['identifier']);

            if (isset($data['quantity_classification']['dynamic_rows']['dynamic_rows'])) {
                $rowData->setClassification($this->serializer->serialize($data['quantity_classification']['dynamic_rows']['dynamic_rows']));
            }
            $rowData->save();
            $this->messageManager->addSuccessMessage(__('List has been successfully saved.'));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addErrorMessage(__('Unable to save the list'));
        }
        return $resultRedirect->setPath('*/index/');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ziffity_CustomFrame::save');
    }
}
