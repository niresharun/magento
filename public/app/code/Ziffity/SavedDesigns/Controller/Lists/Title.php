<?php
namespace Ziffity\SavedDesigns\Controller\Lists;

use Magento\Framework\Controller\Result\JsonFactory;
use Ziffity\SavedDesigns\Model\SavedDesignsFactory;
use Magento\Framework\Message\ManagerInterface;

class Title extends \Magento\Framework\App\Action\Action
{
    /**
     * @var SavedDesignsFactory
     */
	protected $savedDesigns;

	/**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param SavedDesignsFactory $savedDesigns
     * @param ManagerInterface $messageManager
     */
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		JsonFactory $resultJsonFactory,
		SavedDesignsFactory $savedDesigns,
        ManagerInterface $messageManager
		)
	{
		$this->resultJsonFactory = $resultJsonFactory;
		$this->savedDesigns = $savedDesigns;
        $this->messageManager = $messageManager;
		return parent::__construct($context);
	}

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
	public function execute()
	{
		$result = $this->resultJsonFactory->create();
		$requestData = $this->getRequest()->getParams();

		try {
			if($requestData['id'] && $requestData['title']) {
				$savedDesign = $this->savedDesigns->create();
				$savedDesign->load($requestData['id']);
				$savedDesign->setTitle($requestData['title']);
				$savedDesign->save();
                $this->messageManager->addSuccessMessage(__('Successfully updated the title.'));
				$status = 'successfully updated the title.';
				$code= 'success';
			}

		} catch (\Exception $e) {
            $this->messageManager->addSuccessMessage(__('Something went wrong. Please try again later.'));
	        $status = $e->getMessage();
	        $code = 'fail';
	    }



		$data = ['message' => $status , 'code' => $code];
		return $result->setData($data);
	}
}
